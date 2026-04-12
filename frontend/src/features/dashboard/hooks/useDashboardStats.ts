import { useEffect, useState } from "react";
import { dashboardApi, type DashboardStatsResponse } from "../dashboard.api";

export function useDashboardStats() {
  const [data, setData] = useState<DashboardStatsResponse | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    dashboardApi
      .getStats()
      .then((res) => setData(res))
      .catch(() => setError("Impossible de charger les statistiques."))
      .finally(() => setLoading(false));
  }, []);

  return { data, loading, error };
}