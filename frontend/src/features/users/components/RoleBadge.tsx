import type { UserRole } from "../users.types";
import { ROLE_LABEL } from "../users.validators";

export default function RoleBadge({ role }: { role: UserRole }) {
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
      {ROLE_LABEL[role]}
    </span>
  );
}