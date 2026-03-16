export type RepairStatus =
  | "CREATED"
  | "ASSIGNED"
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
};

export type UserLight = {
  id: number;
  firstName: string;
  lastName: string;
  role?: string;
};

export type EquipmentModelLight = { id: number; name: string };
export type IssueLight = { id: number; name: string };

export type RepairOrderRead = {
  id: number; // internal only
  reference: string; // display this to users
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

export type AssignTechnicianPayload = { technicianId: number };
export type UpdateStatusPayload = { status: RepairStatus };

export type TicketResponse = {
  ticketId: number;
  filename: string;
  mimeType: string;
  size: number;
  isSent: boolean;
  version: number;
};

export type TicketRead = {
  id: number;
  filename: string;
  mimeType: string;
  size: number;
  version: number;
  generatedAt: string;
  isSent: boolean;
  sentAt?: string | null;
  viewUrl: string;
  downloadUrl: string;
};