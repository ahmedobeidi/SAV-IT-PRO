export type Paginated<T> = {
  page: number;
  limit: number;
  total: number;
  items: T[];
};

export type EquipmentTypeRead = {
  id: number;
  name: string;
  createdAt?: string;
  updatedAt?: string | null;
};

export type EquipmentBrandRead = {
  id: number;
  name: string;
  equipmentType: EquipmentTypeRead | number; // selon ton serializer
  createdAt?: string;
  updatedAt?: string | null;
};

export type EquipmentModelRead = {
  id: number;
  name: string;
  equipmentBrand: EquipmentBrandRead | number; // selon ton serializer
  createdAt?: string;
  updatedAt?: string | null;
};

export type CreateNamePayload = { name: string };
export type UpdateNamePayload = { name: string };