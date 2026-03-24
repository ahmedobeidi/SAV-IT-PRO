import { useSyncExternalStore } from "react";
import { loadingStore } from "./loading.store";

export function useGlobalLoading() {
  const pending = useSyncExternalStore(
    loadingStore.subscribe,
    loadingStore.getPending,
    loadingStore.getPending
  );

  return { pending, isLoading: pending > 0 };
}
