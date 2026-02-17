import { useEffect, useMemo, useState } from "react";
import { usersApi } from "../users.api";
import type { UsersListResponse } from "../users.types";

export function useUsersList(search: string, page: number, limit: number) {
  const [data, setData] = useState<UsersListResponse | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const key = useMemo(() => `${search}|${page}|${limit}`, [search, page, limit]);

  useEffect(() => {
    let alive = true;
    setLoading(true);
    setError(null);

    usersApi
      .listSilent({ search: search || undefined, page, limit })
      .then((res) => {
        if (!alive) return;
        setData(res);
      })
      .catch(() => {
        if (!alive) return;
        setError("Impossible de charger la liste des utilisateurs.");
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
