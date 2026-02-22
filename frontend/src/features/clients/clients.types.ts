export type ClientRead = {
  id: number;
  firstName: string;
  lastName: string;
  phone: string;
  email?: string | null;
  address?: string | null;
  postalCode?: string | null;
  city?: string | null;
  landlinePhone?: string | null;
  isAnonymized: boolean;
  createdAt?: string;
  updatedAt?: string | null;
};

export type ClientsListResponse = {
  page: number;
  limit: number;
  total: number;
  items: ClientRead[];
};

export type CreateClientPayload = {
  firstName: string;
  lastName: string;
  phone: string;
  email?: string | null;
  address?: string | null;
  postalCode?: string | null;
  city?: string | null;
  landlinePhone?: string | null;
};

export type UpdateClientPayload = Partial<CreateClientPayload>;

export type ClientRepairsResponse = {
  clientId: number;
  items: any[]; // you can type this later when RepairOrder groups are stable
};