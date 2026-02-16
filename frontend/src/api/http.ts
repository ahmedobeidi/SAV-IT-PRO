import axios from "axios";
import type { AxiosError, InternalAxiosRequestConfig } from "axios";
import { authStore } from "../auth/auth.store";
import { authService } from "../auth/auth.service.ts";

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL;

export const http = axios.create({
  baseURL: API_BASE_URL,
  headers: { "Content-Type": "application/json" },
});

// Attach JWT on every request
http.interceptors.request.use((config: InternalAxiosRequestConfig) => {
  const { accessToken } = authStore.getTokens();
  if (accessToken) {
    config.headers.Authorization = `Bearer ${accessToken}`;
  }
  return config;
});

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
  (res) => res,
  async (err: AxiosError) => {
    const original = err.config as any;

    // If not 401 or already retried, fail
    if (err.response?.status !== 401 || original?._retry) {
      throw err;
    }

    original._retry = true;

    const { refreshToken } = authStore.getTokens();
    if (!refreshToken) {
      authStore.clear();
      throw err;
    }

    if (isRefreshing) {
      // Wait until refresh finished, then retry
      return new Promise((resolve, reject) => {
        queueRefresh((newToken) => {
          if (!newToken) return reject(err);
          original.headers.Authorization = `Bearer ${newToken}`;
          resolve(http(original));
        });
      });
    }

    isRefreshing = true;

    try {
      const refreshed = await authService.refresh(refreshToken);
      authStore.setTokens(refreshed.token, refreshed.refresh_token);

      resolveQueue(refreshed.token);

      original.headers.Authorization = `Bearer ${refreshed.token}`;
      return http(original);
    } catch (e) {
      resolveQueue(null);
      authStore.clear();
      throw err;
    } finally {
      isRefreshing = false;
    }
  }
);
