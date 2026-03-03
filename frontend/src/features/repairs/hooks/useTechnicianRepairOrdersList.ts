import { useEffect, useMemo, useState } from "react";
import { repairsApi } from "../repairs.api";
import type { Paginated, RepairOrderRead, RepairStatus } from "../repairs.types";

export function useTechnicianRepairOrdersList(status: RepairStatus | "", page: number, limit: number) {
  const [data, setData] = useState<Paginated<RepairOrderRead> | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [nonce, setNonce] = useState(0);

  const key = useMemo(() => `${status}|${page}|${limit}|${nonce}`, [status, page, limit, nonce]);

  useEffect(() => {
    let alive = true;
    setLoading(true);
    setError(null);

    repairsApi
      .technicianList({ status: status || undefined, page, limit })
      .then((res) => alive && setData(res))
      .catch(() => alive && setError("Impossible de charger tes réparations assignées."))
      .finally(() => alive && setLoading(false));

    return () => { alive = false; };
  }, [key]);

  return { data, loading, error, refresh: () => setNonce((n) => n + 1), setData };
}