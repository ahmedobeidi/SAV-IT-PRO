import { http } from "../../shared/api/http";
import type {
  Paginated,
  RepairOrderRead,
  CreateRepairOrderPayload,
  UpdateRepairOrderPayload,
  AssignTechnicianPayload,
  UpdateStatusPayload,
  RepairStatus,
  TicketRead,
} from "./repairs.types";

export const repairsApi = {
  async create(payload: CreateRepairOrderPayload): Promise<RepairOrderRead> {
    const res = await http.post("/api/repair-orders", payload);
    return res.data;
  },

  async update(id: number, payload: UpdateRepairOrderPayload): Promise<RepairOrderRead> {
    const res = await http.patch(`/api/repair-orders/${id}`, payload);
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

  async technicianList(params: {
    page?: number;
    limit?: number;
    status?: RepairStatus;
  }): Promise<Paginated<RepairOrderRead>> {
    const res = await http.get("/api/technician/repair-orders", { params });
    return res.data;
  },

  async technicianUpdateStatus(
    id: number,
    payload: UpdateStatusPayload,
  ): Promise<RepairOrderRead> {
    const res = await http.patch(
      `/api/technician/repair-orders/${id}/status`,
      payload,
    );
    return res.data;
  },

  async generateCurrentTicket(id: number): Promise<TicketRead> {
    const res = await http.post(`/api/repair-orders/${id}/tickets/generate`);
    return res.data;
  },

  async viewTicketBlob(ticketId: number): Promise<Blob> {
    const res = await http.get(`/api/tickets/${ticketId}/view`, {
      responseType: "blob",
      meta: { skipLoading: true },
    } as any);
    return res.data;
  },
};