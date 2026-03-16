import { useEffect, useMemo, useRef, useState } from "react";
import { clientsApi } from "../clients.api";
import type { ClientsListResponse } from "../clients.types";

export function useClientsList(phone: string, page: number, limit: number) {
  const [data, setData] = useState<ClientsListResponse | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const [nonce, setNonce] = useState(0);
  const reqId = useRef(0);

  const key = useMemo(
    () => `${phone}|${page}|${limit}|${nonce}`,
    [phone, page, limit, nonce]
  );

  useEffect(() => {
    const current = ++reqId.current;

    setLoading(true);
    setError(null);

    clientsApi
      .listSilent({ phone: phone || undefined, page, limit })
      .then((res) => {
        if (current !== reqId.current) return;
        setData(res);
      })
      .catch(() => {
        if (current !== reqId.current) return;
        setError("Impossible de charger la liste des clients.");
      })
      .finally(() => {
        if (current === reqId.current) setLoading(false);
      });
  }, [key]);

  return {
    data,
    loading,
    error,
    refresh: () => setNonce((n) => n + 1),
    setData,
  };
}