import { http } from "../../api/http";

export type IssueRead = { id: number; name: string };

export const issuesApi = {
  async list(params?: { search?: string }): Promise<{ items: IssueRead[] }> {
    const res = await http.get("/api/issues", { params });
    return res.data;
  },
};