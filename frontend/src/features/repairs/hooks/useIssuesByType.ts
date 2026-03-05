import { useEffect, useMemo, useState } from "react";
import { issuesApi, type IssueRead } from "../../issues/issues.api";

export function useIssuesByType(typeId: number | "") {
  const limit = 200;

  const [items, setItems] = useState<IssueRead[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const typeKey = useMemo(() => `${typeId}`, [typeId]);

  useEffect(() => {
    if (!typeId) {
      setItems([]);
      setLoading(false);
      setError(null);
      return;
    }

    let alive = true;
    setLoading(true);
    setError(null);
    setItems([]);

    issuesApi
      .listByType(Number(typeId), { page: 1, limit })
      .then((res) => {
        if (!alive) return;
        setItems(res.items);
      })
      .catch(() => {
        if (!alive) return;
        setError("Impossible de charger les pannes.");
      })
      .finally(() => {
        if (!alive) return;
        setLoading(false);
      });

    return () => {
      alive = false;
    };
  }, [typeKey]);

  return { items, loading, error };
}