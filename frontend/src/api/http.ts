import axios from "axios";
import type { AxiosError, InternalAxiosRequestConfig } from "axios";
import { authStore } from "../features/auth/auth.store";
import { authService } from "../features/auth/auth.service";
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

/** ---------- ✅ Proactive refresh helpers (ADD HERE) ---------- **/
function getJwtExp(token: string): number | null {
  try {
    const payload = JSON.parse(atob(token.split(".")[1]));
    return typeof payload.exp === "number" ? payload.exp : null;
  } catch {
    return null;
  }
}

function isTokenNearExpiry(token: string, seconds = 5) {
  const exp = getJwtExp(token);
  if (!exp) return true;
  const now = Math.floor(Date.now() / 1000);
  return exp - now <= seconds;
}

// Single-flight refresh (so only one refresh runs)
let refreshPromise: Promise<string | null> | null = null;

async function ensureFreshAccessToken(): Promise<string | null> {
  const { accessToken, refreshToken } = authStore.getTokens();
  if (!accessToken || !refreshToken) return null;

  // if token still ok, use it
  if (!isTokenNearExpiry(accessToken, 5)) return accessToken;

  // token is near expiry => refresh once
  if (!refreshPromise) {
    refreshPromise = authService
      .refresh(refreshToken)
      .then((refreshed) => {
        const { role } = authStore.getTokens();
        authStore.setTokens(
          refreshed.token,
          refreshed.refresh_token,
          refreshed.role ?? role
        );
        return refreshed.token;
      })
      .catch(() => {
        authStore.clear();
        return null;
      })
      .finally(() => {
        refreshPromise = null;
      });
  }

  return refreshPromise;
}
/** ---------- end proactive helpers ---------- **/

export const http = axios.create({
  baseURL: API_BASE_URL,
  headers: { "Content-Type": "application/json" },
});

// Handle 401 -> try refresh once -> retry original request (your existing queue)
let isRefreshing = false;
let refreshQueue: Array<(token: string | null) => void> = [];

function queueRefresh(cb: (token: string | null) => void) {
  refreshQueue.push(cb);
}

function resolveQueue(token: string | null) {
  refreshQueue.forEach((cb) => cb(token));
  refreshQueue = [];
}

// Attach JWT + START loading  ✅ (MODIFY THIS INTERCEPTOR)
http.interceptors.request.use(
  async (config: AnyConfig) => {
    // loading: only inc once per "logical request" (even if retried)
    if (shouldTrack(config) && !config.__loadingTracked) {
      loadingStore.inc();
      config.__loadingTracked = true;
    }

    // ✅ For auth endpoints, don’t refresh pre-emptively, just send if exists
    if (isAuthEndpoint(config)) {
      const { accessToken } = authStore.getTokens();
      if (accessToken) config.headers.Authorization = `Bearer ${accessToken}`;
      return config;
    }

    // ✅ Proactive refresh if needed (before sending request)
    const token = await ensureFreshAccessToken();
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
  },
  (error) => {
    loadingStore.dec();
    return Promise.reject(error);
  }
);

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

    if (!willTryRefresh) {
      if (original && shouldTrack(original) && original.__loadingTracked) {
        loadingStore.dec();
        original.__loadingTracked = false;
      }
      throw err;
    }

    original._retry = true;

    const { refreshToken } = authStore.getTokens();
    if (!refreshToken) {
      authStore.clear();

      if (shouldTrack(original) && original.__loadingTracked) {
        loadingStore.dec();
        original.__loadingTracked = false;
      }

      throw err;
    }

    if (isRefreshing) {
      return new Promise((resolve, reject) => {
        queueRefresh((newToken) => {
          if (!newToken) {
            if (shouldTrack(original) && original.__loadingTracked) {
              loadingStore.dec();
              original.__loadingTracked = false;
            }
            return reject(err);
          }

          original.headers.Authorization = `Bearer ${newToken}`;
          resolve(http(original));
        });
      });
    }

    isRefreshing = true;

    try {
      const refreshed = await authService.refresh(refreshToken);

      const { role } = authStore.getTokens();
      authStore.setTokens(
        refreshed.token,
        refreshed.refresh_token,
        refreshed.role ?? role
      );

      resolveQueue(refreshed.token);

      original.headers.Authorization = `Bearer ${refreshed.token}`;
      return http(original);
    } catch (e) {
      resolveQueue(null);
      authStore.clear();

      if (shouldTrack(original) && original.__loadingTracked) {
        loadingStore.dec();
        original.__loadingTracked = false;
      }

      throw e;
    } finally {
      isRefreshing = false;
    }
  }
);
