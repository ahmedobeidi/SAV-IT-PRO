import { http } from "../../shared/api/http";

export type IssueRead = { id: number; name: string };

export type Paginated<T> = {
  page: number;
  limit: number;
  total: number;
  items: T[];
};

export const issuesApi = {
  async listByType(
    typeId: number,
    params?: { search?: string; page?: number; limit?: number }
  ): Promise<Paginated<IssueRead>> {
    const res = await http.get(`/api/equipment-types/${typeId}/issues`, {
      params,
    });
    return res.data;
  },

  async create(
    typeId: number,
    data: { name: string }
  ): Promise<IssueRead> {
    const res = await http.post(`/api/equipment-types/${typeId}/issues`, data);
    return res.data;
  },

  async update(
    issueId: number,
    data: { name: string }
  ): Promise<IssueRead> {
    const res = await http.patch(`/api/issues/${issueId}`, data);
    return res.data;
  },

  async delete(issueId: number): Promise<void> {
    await http.delete(`/api/issues/${issueId}`);
  },
};