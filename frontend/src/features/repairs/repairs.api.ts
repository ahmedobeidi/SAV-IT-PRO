import { http } from "../../api/http";
import type {
  Paginated,
  RepairOrderRead,
  CreateRepairOrderPayload,
  AssignTechnicianPayload,
  UpdateStatusPayload,
  RepairStatus,
  TicketRead,
  GeneratedTicketResponse,
} from "./repairs.types";

export const repairsApi = {
  async create(payload: CreateRepairOrderPayload): Promise<RepairOrderRead> {
    const res = await http.post("/api/repair-orders", payload);
    return res.data;
  },

  async list(params: {
    page?: number;
    limit?: number;
    status?: RepairStatus;
    search?: string;
  }): Promise<Paginated<RepairOrderRead>> {
    const res = await http.get("/api/repair-orders", { params });
    return res.data;
  },

  async listSilent(params: {
    page?: number;
    limit?: number;
    status?: RepairStatus;
    search?: string;
  }): Promise<Paginated<RepairOrderRead>> {
    const res = await http.get("/api/repair-orders", {
      params,
      meta: { skipLoading: true },
    } as any);
    return res.data;
  },

  async assign(id: number, payload: AssignTechnicianPayload): Promise<RepairOrderRead> {
    const res = await http.patch(`/api/repair-orders/${id}/assign`, payload);
    return res.data;
  },

  async staffUpdateStatus(id: number, payload: UpdateStatusPayload): Promise<RepairOrderRead> {
    const res = await http.patch(`/api/repair-orders/${id}/status`, payload);
    return res.data;
  },

  async generateCurrentTicket(id: number): Promise<GeneratedTicketResponse> {
    const res = await http.post(`/api/repair-orders/${id}/tickets/generate`);
    return res.data;
  },

  async sendCurrentTicket(id: number): Promise<{ message: string; ticketId: number; version: number }> {
    const res = await http.post(`/api/repair-orders/${id}/tickets/send`);
    return res.data;
  },

  async listTickets(id: number): Promise<TicketRead[]> {
    const res = await http.get(`/api/repair-orders/${id}/tickets`, {
      meta: { skipLoading: true },
    } as any);
    return res.data;
  },

  async viewTicketBlob(ticketId: number): Promise<Blob> {
    const res = await http.get(`/api/tickets/${ticketId}/view`, {
      responseType: "blob",
      meta: { skipLoading: true },
    } as any);
    return res.data;
  },

  async downloadTicketBlob(ticketId: number): Promise<Blob> {
    const res = await http.get(`/api/tickets/${ticketId}/download`, {
      responseType: "blob",
      meta: { skipLoading: true },
    } as any);
    return res.data;
  },
};