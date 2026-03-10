import { http } from "../../api/http";
import type {
  ChangeMyPasswordPayload,
  CreateUserPayload,
  UpdateUserPayload,
  UserRead,
  UsersListResponse,
} from "./users.types";

export const usersApi = {
  async list(params: { search?: string; page?: number; limit?: number }): Promise<UsersListResponse> {
    const res = await http.get("/api/users", { params });
    return res.data;
  },

  async listSilent(params: { search?: string; page?: number; limit?: number }): Promise<UsersListResponse> {
    const res = await http.get("/api/users", {
      params,
      meta: { skipLoading: true },
    } as any);
    return res.data;
  },

  async show(id: number): Promise<UserRead> {
    const res = await http.get(`/api/users/${id}`);
    return res.data;
  },

  async create(payload: CreateUserPayload): Promise<{ message: string; user: UserRead }> {
    const res = await http.post("/api/users", payload);
    return res.data;
  },

  async update(id: number, payload: UpdateUserPayload): Promise<UserRead> {
    const res = await http.patch(`/api/users/${id}`, payload);
    return res.data;
  },

  async setActive(id: number, isActive: boolean): Promise<UserRead> {
    const res = await http.patch(`/api/users/${id}/block`, { isActive });
    return res.data;
  },

  async anonymize(id: number): Promise<UserRead> {
    const res = await http.patch(`/api/users/${id}/anonymize`);
    return res.data;
  },

  async changeMyPassword(payload: ChangeMyPasswordPayload): Promise<{ message: string }> {
    const res = await http.patch("/api/me/password", payload);
    return res.data;
  },
};