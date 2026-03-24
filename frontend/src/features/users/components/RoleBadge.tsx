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
        background: "var(--neutral-muted)",
      }}
    >
      {ROLE_LABEL[role]}
    </span>
  );
}
