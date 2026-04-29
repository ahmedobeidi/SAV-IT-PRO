import { useEffect, useMemo, useState } from "react";
import { repairsApi } from "../repairs.api";
import type { Paginated, RepairOrderRead, RepairStatus } from "../repairs.types";

export function useRepairOrdersList(
  search: string,
  status: RepairStatus | "",
  page: number,
  limit: number
) {
  const [data, setData] = useState<Paginated<RepairOrderRead> | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [nonce, setNonce] = useState(0);

  const key = useMemo(
    () => `${search}|${status}|${page}|${limit}|${nonce}`,
    [search, status, page, limit, nonce]
  );

  useEffect(() => {
    let alive = true;
    setLoading(true);
    setError(null);

    repairsApi
      .list({
        search: search || undefined,
        status: status || undefined,
        page,
        limit,
      })
      .then((res) => {
        if (alive) setData(res);
      })
      .catch(() => {
        if (alive) setError("Impossible de charger les réparations.");
      })
      .finally(() => {
        if (alive) setLoading(false);
      });

    return () => {
      alive = false;
    };
  }, [key]);

  return {
    data,
    loading,
    error,
    refresh: () => setNonce((n) => n + 1),
    setData,
  };
}