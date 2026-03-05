import type { CreateUserPayload, UpdateUserPayload, UserRole } from "./users.types";

export function isEmail(value: string): boolean {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim());
}

export function validateCreate(payload: CreateUserPayload): Record<string, string> {
  const e: Record<string, string> = {};
  if (!payload.firstName.trim()) e.firstName = "Prénom obligatoire.";
  if (!payload.lastName.trim()) e.lastName = "Nom obligatoire.";
  if (!payload.email.trim() || !isEmail(payload.email)) e.email = "Email invalide.";
  if (!payload.password || payload.password.length < 8) e.password = "Mot de passe: 8 caractères minimum.";
  if (!payload.role) e.role = "Rôle obligatoire.";
  return e;
}

export function validateUpdate(payload: UpdateUserPayload): Record<string, string> {
  const e: Record<string, string> = {};
  if (payload.email !== undefined && payload.email !== "" && !isEmail(payload.email)) e.email = "Email invalide.";
  if (payload.password !== undefined && payload.password !== "" && payload.password.length < 8)
    e.password = "Mot de passe: 8 caractères minimum.";
  return e;
}

export const ALL_ROLES: UserRole[] = [
  "ROLE_SUPER_ADMIN",
  "ROLE_ADMIN",
  "ROLE_TECHNICIAN",
  "ROLE_RECEPTION",
];

export const ROLE_LABEL: Record<UserRole, string> = {
  ROLE_SUPER_ADMIN: "Super",
  ROLE_ADMIN: "Admin",
  ROLE_TECHNICIAN: "Technicien",
  ROLE_RECEPTION: "Accueil",
};
