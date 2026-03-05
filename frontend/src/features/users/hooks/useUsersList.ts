import { useEffect, useMemo, useRef, useState } from "react";
import { usersApi } from "../users.api";
import type { UsersListResponse } from "../users.types";

export function useUsersList(search: string, page: number, limit: number) {
  const [data, setData] = useState<UsersListResponse | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const [nonce, setNonce] = useState(0);
  const reqId = useRef(0);

  const key = useMemo(() => `${search}|${page}|${limit}|${nonce}`, [search, page, limit, nonce]);

  useEffect(() => {
    const current = ++reqId.current;

    setLoading(true);
    setError(null);

    usersApi
      .listSilent({ search: search || undefined, page, limit })
      .then((res) => {
        if (current !== reqId.current) return;
        setData(res);
      })
      .catch(() => {
        if (current !== reqId.current) return;
        setError("Impossible de charger la liste des utilisateurs.");
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