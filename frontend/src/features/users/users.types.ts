export type UserRole =
  | "ROLE_SUPER_ADMIN"
  | "ROLE_ADMIN"
  | "ROLE_TECHNICIAN"
  | "ROLE_RECEPTION";

export type UserRead = {
  id: number;
  firstName: string;
  lastName: string;
  email: string;
  role: UserRole;
  isActive: boolean;
  isAnonymized: boolean;
  passwordSetupRequired: boolean;
  createdAt: string;
  updatedAt: string | null;
};

export type UsersListResponse = {
  page: number;
  limit: number;
  total: number;
  items: UserRead[];
};

export type CreateUserPayload = {
  firstName: string;
  lastName: string;
  email: string;
  role: UserRole;
};

export type UpdateUserPayload = {
  firstName?: string;
  lastName?: string;
  email?: string;
  password?: string;
  role?: UserRole;
  isActive?: boolean;
};