import type { CreateClientPayload, UpdateClientPayload } from "./clients.types";

export function validateCreateClient(p: CreateClientPayload): Record<string, string> {
  const e: Record<string, string> = {};
  if (!p.firstName.trim()) e.firstName = "Prénom obligatoire.";
  if (!p.lastName.trim()) e.lastName = "Nom obligatoire.";
  if (!p.phone.trim()) e.phone = "Téléphone obligatoire.";
  if (p.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(p.email.trim())) e.email = "Email invalide.";
  return e;
}

export function validateUpdateClient(p: UpdateClientPayload): Record<string, string> {
  const e: Record<string, string> = {};
  if (p.email !== undefined && p.email !== null && p.email !== "" && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(p.email.trim()))
    e.email = "Email invalide.";
  return e;
}