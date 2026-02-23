import { useEffect, useMemo, useState } from "react";
import { equipmentApi } from "../equipment.api";
import type { Paginated, EquipmentBrandRead } from "../equipment.types";

export function useEquipmentBrands(typeId: number, search: string, page: number, limit: number) {
  const [data, setData] = useState<Paginated<EquipmentBrandRead> | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const [nonce, setNonce] = useState(0);
  const key = useMemo(() => `${typeId}|${search}|${page}|${limit}|${nonce}`, [typeId, search, page, limit, nonce]);

  useEffect(() => {
    let alive = true;
    setLoading(true);
    setError(null);

    equipmentApi
      .listBrands(typeId, { search: search || undefined, page, limit })
      .then((res) => alive && setData(res))
      .catch(() => alive && setError("Impossible de charger les marques."))
      .finally(() => alive && setLoading(false));

    return () => { alive = false; };
  }, [key]);

  return { data, loading, error, refresh: () => setNonce((n) => n + 1), setData };
}