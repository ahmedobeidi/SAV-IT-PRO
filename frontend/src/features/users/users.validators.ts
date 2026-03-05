import type { CreateUserPayload, UpdateUserPayload, UserRole } from "./users.types";

export function isEmail(value: string): boolean {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim());
}

function strongPassword(value: string): boolean {
  return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/.test(value);
}

export function validateCreate(payload: CreateUserPayload): Record<string, string> {
  const e: Record<string, string> = {};
  if (!payload.firstName.trim()) e.firstName = "Prénom obligatoire.";
  if (!payload.lastName.trim()) e.lastName = "Nom obligatoire.";
  if (!payload.email.trim() || !isEmail(payload.email)) e.email = "Email invalide.";
  if (!payload.password || !strongPassword(payload.password)) e.password = "8 caractères min + 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial.";
  if (!payload.role) e.role = "Rôle obligatoire.";
  return e;
}

export function validateUpdate(payload: UpdateUserPayload): Record<string, string> {
  const e: Record<string, string> = {};
  if (payload.email !== undefined && payload.email !== "" && !isEmail(payload.email)) e.email = "Email invalide.";
  if (payload.password !== undefined && payload.password !== "" && !strongPassword(payload.password))
    e.password = "8 caractères min + 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial.";
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
