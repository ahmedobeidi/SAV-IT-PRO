import { useSyncExternalStore } from "react";
import { authStore } from "./auth.store";

export function useAuth() {
  const tokens = useSyncExternalStore(
    authStore.subscribe,
    authStore.getTokens,
    authStore.getTokens,
  );

  return {
    ...tokens,
    isLoggedIn: !!tokens.accessToken,
  };
}