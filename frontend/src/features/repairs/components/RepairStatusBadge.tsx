import type { RepairStatus } from "../repairs.types";
import { getStatusLabel } from "../utils/statusTranslations";

const shortLabels: Record<RepairStatus, string> = {
  CREATED: "Créé",
  IN_PROGRESS: "En cours",
  WAITING_PARTS: "Pièces",
  DONE: "Terminé",
  DELIVERED: "Livré",
  CANCELED: "Annulé",
};

export default function RepairStatusBadge({ status }: { status: RepairStatus }) {
  const label = getStatusLabel(status);
  const shortLabel = shortLabels[status];

  return (
    <span className="repair-status-badge" title={label} aria-label={label}>
      <span className="repair-status-badge__full">{label}</span>
      <span className="repair-status-badge__short">{shortLabel}</span>
    </span>
  );
}
