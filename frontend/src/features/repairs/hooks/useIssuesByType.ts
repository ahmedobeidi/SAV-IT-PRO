import { useState, useEffect, useCallback } from "react";
import { issuesApi, type IssueRead } from "../../issues/issues.api";

export function useIssuesByType(typeId: number | "") {
  const [items, setItems] = useState<IssueRead[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const fetch = useCallback(async () => {
    if (!typeId) {
      setItems([]);
      return;
    }
    setLoading(true);
    try {
      const data = await issuesApi.listByType(typeId);
      setItems(data.items);
      setError(null);
    } catch {
      setError("Erreur lors du chargement des pannes.");
    } finally {
      setLoading(false);
    }
  }, [typeId]);

  useEffect(() => {
    fetch();
  }, [fetch]);

  return { items, loading, error, refresh: fetch };
}