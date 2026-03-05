import { useEffect, useMemo, useRef, useState } from "react";
import { equipmentApi } from "../equipment.api";
import type { Paginated, EquipmentModelRead } from "../equipment.types";

export function useEquipmentModels(
  brandId: number,
  search: string,
  page: number,
  limit: number
) {
  const [data, setData] = useState<Paginated<EquipmentModelRead> | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const [nonce, setNonce] = useState(0);
  const reqId = useRef(0);

  const key = useMemo(
    () => `${brandId}|${search}|${page}|${limit}|${nonce}`,
    [brandId, search, page, limit, nonce]
  );

  useEffect(() => {
    const current = ++reqId.current;

    const timeout = window.setTimeout(async () => {
      setLoading(true);
      setError(null);

      try {
        const res = await equipmentApi.listModelsSilent(brandId, {
          search: search || undefined,
          page,
          limit,
        });

        if (current !== reqId.current) return;
        setData(res);
      } catch {
        if (current !== reqId.current) return;
        setError("Impossible de charger les modèles.");
      } finally {
        if (current === reqId.current) setLoading(false);
      }
    }, 300);

    return () => window.clearTimeout(timeout);
  }, [key]);

  return { data, loading, error, refresh: () => setNonce((n) => n + 1), setData };
}