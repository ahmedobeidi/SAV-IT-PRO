export type RepairStatus =
  | "CREATED"
  | "IN_PROGRESS"
  | "WAITING_PARTS"
  | "DONE"
  | "DELIVERED"
  | "CANCELED";

export type ClientLight = {
  id: number;
  firstName: string;
  lastName: string;
  phone: string;
  email?: string | null;
};

export type UserLight = {
  id: number;
  firstName: string;
  lastName: string;
  role?: string;
};

export type EquipmentTypeLight = {
  id: number;
  name: string;
};

export type EquipmentBrandLight = {
  id: number;
  name: string;
  equipmentType?: EquipmentTypeLight;
};

export type EquipmentModelLight = {
  id: number;
  name: string;
  equipmentBrand?: EquipmentBrandLight;
};

export type IssueLight = {
  id: number;
  name: string;
};

export type RepairOrderRead = {
  id: number;
  reference: string;
  status: RepairStatus;
  price: number;
  deposit?: number | null;
  description?: string | null;
  createdAt?: string;
  updatedAt?: string | null;

  createdFor: ClientLight;
  createdBy: UserLight;
  assignedTo?: UserLight | null;

  equipmentModel: EquipmentModelLight;
  issue: IssueLight;
};

export type Paginated<T> = {
  page: number;
  limit: number;
  total: number;
  items: T[];
};

export type CreateRepairOrderPayload = {
  clientId: number;
  equipmentModelId: number;
  issueId: number;
  price: number;
  deposit?: number | null;
  description?: string | null;
};

export type AssignTechnicianPayload = { technicianId: number | null };
export type UpdateStatusPayload = { status: RepairStatus };

export type TicketRead = {
  id: number;
  filename: string;
  mimeType: string;
  size: number;
  generatedAt: string;
  isCurrent: boolean;
  viewUrl: string;
};

export type UpdateRepairOrderPayload = {
  equipmentModelId: number;
  issueId: number;
  price: number;
  deposit?: number | null;
  description?: string | null;
};

export type GeneratedTicketResponse = TicketRead;