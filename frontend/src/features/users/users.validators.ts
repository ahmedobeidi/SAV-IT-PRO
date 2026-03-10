import type {
  ChangeMyPasswordPayload,
  CreateUserPayload,
  UpdateUserPayload,
  UserRole,
} from "./users.types";

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
  if (!payload.role) e.role = "Rôle obligatoire.";
  return e;
}

export function validateUpdate(payload: UpdateUserPayload): Record<string, string> {
  const e: Record<string, string> = {};

  if (payload.firstName !== undefined && !payload.firstName.trim()) {
    e.firstName = "Prénom obligatoire.";
  }

  if (payload.lastName !== undefined && !payload.lastName.trim()) {
    e.lastName = "Nom obligatoire.";
  }

  if (payload.email !== undefined) {
    if (!payload.email.trim()) e.email = "Email obligatoire.";
    else if (!isEmail(payload.email)) e.email = "Email invalide.";
  }

  return e;
}

export function validateChangeMyPassword(
  payload: ChangeMyPasswordPayload,
): Record<string, string> {
  const e: Record<string, string> = {};

  if (!payload.currentPassword.trim()) {
    e.currentPassword = "Mot de passe actuel obligatoire.";
  }

  if (!payload.newPassword.trim()) {
    e.newPassword = "Nouveau mot de passe obligatoire.";
  } else if (!strongPassword(payload.newPassword)) {
    e.newPassword =
      "8 caractères min + 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial.";
  }

  if (!payload.confirmPassword.trim()) {
    e.confirmPassword = "Confirmation obligatoire.";
  } else if (payload.newPassword !== payload.confirmPassword) {
    e.confirmPassword = "La confirmation ne correspond pas.";
  }

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