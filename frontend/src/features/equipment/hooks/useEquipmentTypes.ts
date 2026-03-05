import { useEffect, useMemo, useRef, useState } from "react";
import { equipmentApi } from "../equipment.api";
import type { Paginated, EquipmentTypeRead } from "../equipment.types";

export function useEquipmentTypes(search: string, page: number, limit: number) {
  const [data, setData] = useState<Paginated<EquipmentTypeRead> | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const [nonce, setNonce] = useState(0);

  // ✅ like your client search: ignore outdated responses
  const reqId = useRef(0);

  // key changes when filters change (including manual refresh)
  const key = useMemo(
    () => `${search}|${page}|${limit}|${nonce}`,
    [search, page, limit, nonce]
  );

  useEffect(() => {
    const current = ++reqId.current;

    // ✅ debounce (same idea as your client search)
    const timeout = window.setTimeout(async () => {
      setLoading(true);
      setError(null);

      try {
        const res = await equipmentApi.listTypesSilent({
          search: search || undefined,
          page,
          limit,
        });

        if (current !== reqId.current) return;
        setData(res);
      } catch {
        if (current !== reqId.current) return;
        setError("Impossible de charger les types.");
      } finally {
        if (current === reqId.current) setLoading(false);
      }
    }, 300);

    return () => window.clearTimeout(timeout);
  }, [key]);

  return { data, loading, error, refresh: () => setNonce((n) => n + 1), setData };
}