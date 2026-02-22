import { useEffect, useMemo, useState } from "react";
import { clientsApi } from "../clients.api";
import type { ClientsListResponse } from "../clients.types";

export function useClientsList(page: number, limit: number) {
  const [data, setData] = useState<ClientsListResponse | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const key = useMemo(() => `${page}|${limit}`, [page, limit]);

  useEffect(() => {
    let alive = true;
    setLoading(true);
    setError(null);

    clientsApi
      .list({ page, limit })
      .then((res) => alive && setData(res))
      .catch(() => alive && setError("Impossible de charger les clients."))
      .finally(() => alive && setLoading(false));

    return () => {
      alive = false;
    };
  }, [key]);

  return { data, loading, error };
}