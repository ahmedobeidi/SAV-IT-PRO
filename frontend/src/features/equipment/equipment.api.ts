import { http } from "../../shared/api/http";
import type {
  Paginated,
  EquipmentTypeRead,
  EquipmentBrandRead,
  EquipmentModelRead,
  CreateNamePayload,
  UpdateNamePayload,
} from "./equipment.types";

export const equipmentApi = {
  // TYPES
  async listTypes(params: { search?: string; page?: number; limit?: number }): Promise<Paginated<EquipmentTypeRead>> {
    const res = await http.get("/api/equipment-types", { params });
    return res.data;
  },

  // ✅ SAME return type + skipLoading
  async listTypesSilent(params: { search?: string; page?: number; limit?: number }): Promise<Paginated<EquipmentTypeRead>> {
    const res = await http.get("/api/equipment-types", {
      params,
      meta: { skipLoading: true },
    } as any);
    return res.data;
  },

  async createType(payload: CreateNamePayload): Promise<EquipmentTypeRead> {
    const res = await http.post("/api/equipment-types", payload);
    return res.data;
  },

  async updateType(id: number, payload: UpdateNamePayload): Promise<EquipmentTypeRead> {
    const res = await http.patch(`/api/equipment-types/${id}`, payload);
    return res.data;
  },

  async deleteType(id: number): Promise<void> {
    await http.delete(`/api/equipment-types/${id}`);
  },

  // BRANDS (par type)
  async listBrands(
    typeId: number,
    params: { search?: string; page?: number; limit?: number }
  ): Promise<Paginated<EquipmentBrandRead>> {
    const res = await http.get(`/api/equipment-types/${typeId}/brands`, { params });
    return res.data;
  },

  // ✅ SAME return type + skipLoading
  async listBrandsSilent(
    typeId: number,
    params: { search?: string; page?: number; limit?: number }
  ): Promise<Paginated<EquipmentBrandRead>> {
    const res = await http.get(`/api/equipment-types/${typeId}/brands`, {
      params,
      meta: { skipLoading: true },
    } as any);
    return res.data;
  },

  async createBrand(typeId: number, payload: CreateNamePayload): Promise<EquipmentBrandRead> {
    const res = await http.post(`/api/equipment-types/${typeId}/brands`, payload);
    return res.data;
  },

  async updateBrand(id: number, payload: UpdateNamePayload): Promise<EquipmentBrandRead> {
    const res = await http.patch(`/api/equipment-brands/${id}`, payload);
    return res.data;
  },

  async deleteBrand(id: number): Promise<void> {
    await http.delete(`/api/equipment-brands/${id}`);
  },

  // MODELS (par brand)
  async listModels(
    brandId: number,
    params: { search?: string; page?: number; limit?: number }
  ): Promise<Paginated<EquipmentModelRead>> {
    const res = await http.get(`/api/equipment-brands/${brandId}/models`, { params });
    return res.data;
  },

  // ✅ SAME return type + skipLoading
  async listModelsSilent(
    brandId: number,
    params: { search?: string; page?: number; limit?: number }
  ): Promise<Paginated<EquipmentModelRead>> {
    const res = await http.get(`/api/equipment-brands/${brandId}/models`, {
      params,
      meta: { skipLoading: true },
    } as any);
    return res.data;
  },

  async createModel(brandId: number, payload: CreateNamePayload): Promise<EquipmentModelRead> {
    const res = await http.post(`/api/equipment-brands/${brandId}/models`, payload);
    return res.data;
  },

  async updateModel(id: number, payload: UpdateNamePayload): Promise<EquipmentModelRead> {
    const res = await http.patch(`/api/equipment-models/${id}`, payload);
    return res.data;
  },

  async deleteModel(id: number): Promise<void> {
    await http.delete(`/api/equipment-models/${id}`);
  },
};