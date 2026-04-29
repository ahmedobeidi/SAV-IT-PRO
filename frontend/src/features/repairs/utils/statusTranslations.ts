import type { RepairStatus } from "../repairs.types";

export function getStatusLabel(status: RepairStatus): string {
  const labels: Record<RepairStatus, string> = {
    CREATED: "Créé",
    IN_PROGRESS: "En cours",
    WAITING_PARTS: "Attente pièces",
    DONE: "Terminé",
    DELIVERED: "Livré",
    CANCELED: "Annulé",
  };
  return labels[status];
}
