import type { CreateRepairOrderPayload, UpdateRepairOrderPayload } from "./repairs.types";

export function validateCreateRepair(p: CreateRepairOrderPayload): Record<string, string> {
  const e: Record<string, string> = {};
  if (!p.clientId) e.clientId = "Client obligatoire.";
  if (!p.equipmentModelId) e.equipmentModelId = "Modèle obligatoire.";
  if (!p.issueId) e.issueId = "Panne obligatoire.";

  if (p.price === undefined || p.price === null || Number.isNaN(p.price)) e.price = "Prix invalide.";
  if (p.price < 0) e.price = "Prix doit être ≥ 0.";

  if (p.deposit !== undefined && p.deposit !== null && p.deposit < 0) e.deposit = "Acompte doit être ≥ 0.";

  if (p.description && p.description.length > 5000) e.description = "Description trop longue (max 5000).";
  return e;
}

export function validateUpdateRepair(p: UpdateRepairOrderPayload): Record<string, string> {
  const e: Record<string, string> = {};

  if (!p.issueId) e.issueId = "Panne obligatoire.";

  if (p.price === undefined || p.price === null || Number.isNaN(p.price)) {
    e.price = "Prix invalide.";
  }
  if (p.price < 0) {
    e.price = "Prix doit être ≥ 0.";
  }

  if (p.deposit !== undefined && p.deposit !== null && p.deposit < 0) {
    e.deposit = "Acompte doit être ≥ 0.";
  }

  if (p.description && p.description.length > 5000) {
    e.description = "Description trop longue (max 5000).";
  }

  return e;
}

export function mapApiError(e: any): string {
  const s = e?.response?.status;
  if (s === 401) return "Session expirée.";
  if (s === 403) return "Accès interdit.";
  if (s === 404) return "Ressource introuvable.";
  if (s === 422) return "Validation échouée.";
  if (s === 409) return e?.response?.data?.message ?? "Action impossible.";
  if (s === 500) return "Erreur serveur.";
  return "Erreur inattendue.";
}