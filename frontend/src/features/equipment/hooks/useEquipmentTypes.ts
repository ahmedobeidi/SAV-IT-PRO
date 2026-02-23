import { useEffect, useMemo, useState } from "react";
import { equipmentApi } from "../equipment.api";
import type { Paginated, EquipmentTypeRead } from "../equipment.types";

export function useEquipmentTypes(search: string, page: number, limit: number) {
  const [data, setData] = useState<Paginated<EquipmentTypeRead> | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const [nonce, setNonce] = useState(0);
  const key = useMemo(() => `${search}|${page}|${limit}|${nonce}`, [search, page, limit, nonce]);

  useEffect(() => {
    let alive = true;
    setLoading(true);
    setError(null);

    equipmentApi
      .listTypes({ search: search || undefined, page, limit })
      .then((res) => alive && setData(res))
      .catch(() => alive && setError("Impossible de charger les types."))
      .finally(() => alive && setLoading(false));

    return () => { alive = false; };
  }, [key]);

  return { data, loading, error, refresh: () => setNonce((n) => n + 1), setData };
}