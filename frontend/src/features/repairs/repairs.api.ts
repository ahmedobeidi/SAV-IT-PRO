import { http } from "../../api/http";
import type {
  Paginated,
  RepairOrderRead,
  CreateRepairOrderPayload,
  AssignTechnicianPayload,
  UpdateStatusPayload,
  TicketResponse,
  RepairStatus,
} from "./repairs.types";

export const repairsApi = {
  // STAFF (admin/reception)
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

  // ✅ same return type, but no GlobalLoadingOverlay
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

  // TECHNICIAN
  async technicianList(params: {
    page?: number;
    limit?: number;
    status?: RepairStatus;
  }): Promise<Paginated<RepairOrderRead>> {
    const res = await http.get("/api/technician/repair-orders", { params });
    return res.data;
  },

  // (optional) also add technicianListSilent if you have a search box there later

  async technicianUpdateStatus(id: number, payload: UpdateStatusPayload): Promise<RepairOrderRead> {
    const res = await http.patch(`/api/technician/repair-orders/${id}/status`, payload);
    return res.data;
  },

  // EPIC 6
  async generateTicket(id: number): Promise<TicketResponse> {
    const res = await http.post(`/api/repair-orders/${id}/ticket`);
    return res.data;
  },

  async sendTicket(id: number): Promise<{ message: string }> {
    const res = await http.post(`/api/repair-orders/${id}/ticket/send`);
    return res.data;
  },
};