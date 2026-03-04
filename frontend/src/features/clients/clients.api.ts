import { http } from "../../api/http";
import type {
  ClientRead,
  ClientsListResponse,
  CreateClientPayload,
  UpdateClientPayload,
  ClientRepairsResponse,
} from "./clients.types";

export const clientsApi = {
  async list(params: { phone?: string; page?: number; limit?: number }): Promise<ClientsListResponse> {
    const res = await http.get("/api/clients", { params });
    return res.data;
  },

  async listSilent(params: { phone?: string; page?: number; limit?: number }): Promise<ClientsListResponse> {
    const res = await http.get("/api/clients", {
      params,
      meta: { skipLoading: true }, // ✅ same behavior as users
    } as any);
    return res.data;
  },

  // you can keep this if you still use it elsewhere
  async searchByPhone(phone: string): Promise<ClientRead> {
    const data = await this.list({ phone, page: 1, limit: 1 });
    return data.items?.[0] ?? null;
  },

  async show(id: number): Promise<ClientRead> {
    const res = await http.get(`/api/clients/${id}`);
    return res.data;
  },

  async create(payload: CreateClientPayload): Promise<ClientRead> {
    const res = await http.post("/api/clients", payload);
    return res.data;
  },

  async update(id: number, payload: UpdateClientPayload): Promise<ClientRead> {
    const res = await http.patch(`/api/clients/${id}`, payload);
    return res.data;
  },

  async anonymize(id: number): Promise<ClientRead> {
    const res = await http.patch(`/api/clients/${id}/anonymize`);
    return res.data;
  },

  async repairs(id: number): Promise<ClientRepairsResponse> {
    const res = await http.get(`/api/clients/${id}/repairs`);
    return res.data;
  },
};