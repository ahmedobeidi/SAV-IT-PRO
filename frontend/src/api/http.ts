import axios from "axios";
import type { AxiosError, InternalAxiosRequestConfig } from "axios";
import { authStore } from "../auth/auth.store";
import { authService } from "../auth/auth.service";
import { loadingStore } from "../ui/loading.store";

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL;

type AnyConfig = InternalAxiosRequestConfig & {
  meta?: { skipLoading?: boolean };
  __loadingTracked?: boolean; // prevents double inc on retry
  _retry?: boolean; // prevents infinite refresh retry
};

function shouldTrack(config: AnyConfig) {
  return config?.meta?.skipLoading !== true;
}

// Extra safety: never run refresh logic for auth endpoints
function isAuthEndpoint(config?: AnyConfig) {
  const url = config?.url || "";
  return (
    url.includes("/api/auth/login") ||
    url.includes("/api/auth/refresh") ||
    url.includes("/api/auth/logout")
  );
}

export const http = axios.create({
  baseURL: API_BASE_URL,
  headers: { "Content-Type": "application/json" },
});

// Attach JWT + START loading
http.interceptors.request.use(
  (config: AnyConfig) => {
    // loading: only inc once per "logical request" (even if retried)
    if (shouldTrack(config) && !config.__loadingTracked) {
      loadingStore.inc();
      config.__loadingTracked = true;
    }

    // auth header
    const { accessToken } = authStore.getTokens();
    if (accessToken) {
      config.headers.Authorization = `Bearer ${accessToken}`;
    }

    return config;
  },
  (error) => {
    // if request never got config tracked properly, just try to dec safely
    loadingStore.dec();
    return Promise.reject(error);
  }
);

// Handle 401 -> try refresh once -> retry original request
let isRefreshing = false;
let refreshQueue: Array<(token: string | null) => void> = [];

function queueRefresh(cb: (token: string | null) => void) {
  refreshQueue.push(cb);
}

function resolveQueue(token: string | null) {
  refreshQueue.forEach((cb) => cb(token));
  refreshQueue = [];
}

http.interceptors.response.use(
  (res) => {
    const cfg = res.config as AnyConfig;

    // FINISH loading
    if (shouldTrack(cfg) && cfg.__loadingTracked) {
      loadingStore.dec();
      cfg.__loadingTracked = false;
    }

    return res;
  },
  async (err: AxiosError) => {
    const original = err.config as AnyConfig;
    const status = err.response?.status;

    // Only try refresh on 401, only once, and never for auth endpoints
    const willTryRefresh =
      status === 401 && !original?._retry && !isAuthEndpoint(original);

    // If not going through refresh, END loading now
    if (!willTryRefresh) {
      if (original && shouldTrack(original) && original.__loadingTracked) {
        loadingStore.dec();
        original.__loadingTracked = false;
      }
      throw err;
    }

    // mark retry
    original._retry = true;

    const { refreshToken } = authStore.getTokens();
    if (!refreshToken) {
      authStore.clear();

      // END loading (because we won’t retry)
      if (shouldTrack(original) && original.__loadingTracked) {
        loadingStore.dec();
        original.__loadingTracked = false;
      }

      throw err;
    }

    // If a refresh is already running, wait for it
    if (isRefreshing) {
      return new Promise((resolve, reject) => {
        queueRefresh((newToken) => {
          if (!newToken) {
            // END loading (because request will fail)
            if (shouldTrack(original) && original.__loadingTracked) {
              loadingStore.dec();
              original.__loadingTracked = false;
            }
            return reject(err);
          }

          original.headers.Authorization = `Bearer ${newToken}`;
          resolve(http(original)); // request interceptor won’t inc again due to __loadingTracked
        });
      });
    }

    isRefreshing = true;

    try {
      // IMPORTANT: authService.refresh should use rawHttp (no interceptors)
      const refreshed = await authService.refresh(refreshToken);
      authStore.setTokens(refreshed.token, refreshed.refresh_token, refreshed.role);

      resolveQueue(refreshed.token);

      original.headers.Authorization = `Bearer ${refreshed.token}`;
      return http(original);
    } catch (e) {
      resolveQueue(null);
      authStore.clear();

      // END loading (because retry chain ends here)
      if (shouldTrack(original) && original.__loadingTracked) {
        loadingStore.dec();
        original.__loadingTracked = false;
      }

      throw e; // ✅ throw the refresh error (real reason)
    } finally {
      isRefreshing = false;
    }
  }
);
