import { useEffect, useMemo, useState } from "react";
import { equipmentApi } from "../equipment.api";
import type { Paginated, EquipmentModelRead } from "../equipment.types";

export function useEquipmentModels(brandId: number, search: string, page: number, limit: number) {
  const [data, setData] = useState<Paginated<EquipmentModelRead> | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const [nonce, setNonce] = useState(0);
  const key = useMemo(() => `${brandId}|${search}|${page}|${limit}|${nonce}`, [brandId, search, page, limit, nonce]);

  useEffect(() => {
    let alive = true;
    setLoading(true);
    setError(null);

    equipmentApi
      .listModels(brandId, { search: search || undefined, page, limit })
      .then((res) => alive && setData(res))
      .catch(() => alive && setError("Impossible de charger les modèles."))
      .finally(() => alive && setLoading(false));

    return () => { alive = false; };
  }, [key]);

  return { data, loading, error, refresh: () => setNonce((n) => n + 1), setData };
}