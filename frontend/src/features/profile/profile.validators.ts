import type { ChangeMyPasswordPayload } from "./profile.types";

function strongPassword(value: string): boolean {
  return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/.test(value);
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