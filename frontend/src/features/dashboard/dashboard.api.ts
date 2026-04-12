import { http } from "../../shared/api/http";

export type DashboardStatsResponse = {
  clients: number;
  repairOrders: number;
};

export const dashboardApi = {
  async getStats(): Promise<DashboardStatsResponse> {
    const res = await http.get("/api/dashboard/stats");
    return res.data;
  },
};