import type { RepairStatus } from "../repairs.types";
import { getStatusLabel } from "../utils/statusTranslations";

export default function RepairStatusBadge({ status }: { status: RepairStatus }) {
  const label = getStatusLabel(status);

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