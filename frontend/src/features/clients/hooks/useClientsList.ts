import { useEffect, useMemo, useState } from "react";
import { clientsApi } from "../clients.api";
import type { ClientsListResponse } from "../clients.types";

export function useClientsList(phone: string, page: number, limit: number) {
  const [data, setData] = useState<ClientsListResponse | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const key = useMemo(() => `${phone}|${page}|${limit}`, [phone, page, limit]);

  useEffect(() => {
    let alive = true;
    setLoading(true);
    setError(null);

    clientsApi
      .listSilent({ phone: phone || undefined, page, limit })
      .then((res) => {
        if (!alive) return;
        setData(res);
      })
      .catch(() => {
        if (!alive) return;
        setError("Impossible de charger la liste des clients.");
      })
      .finally(() => {
        if (!alive) return;
        setLoading(false);
      });

    return () => {
      alive = false;
    };
  }, [key]);

  return { data, loading, error, refresh: () => setData(null) };
}