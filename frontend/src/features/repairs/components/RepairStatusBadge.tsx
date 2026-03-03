import type { RepairStatus } from "../repairs.types";

export default function RepairStatusBadge({ status }: { status: RepairStatus }) {
  const label =
    status === "CREATED" ? "Créé" :
    status === "ASSIGNED" ? "Assigné" :
    status === "IN_PROGRESS" ? "En cours" :
    status === "WAITING_PARTS" ? "Attente pièces" :
    status === "DONE" ? "Terminé" :
    status === "DELIVERED" ? "Livré" :
    "Annulé";

  return (
    <span
      style={{
        padding: "4px 8px",
        borderRadius: 999,
        border: "1px solid var(--border)",
        fontSize: 12,
        background: "rgba(255,255,255,0.04)",
      }}
    >
      {label}
    </span>
  );
}