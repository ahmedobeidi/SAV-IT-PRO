import type { UserRole } from "../users.types";

export default function RoleBadge({ role }: { role: UserRole }) {
  const label =
    role === "ROLE_SUPER_ADMIN" ? "Super Admin" :
    role === "ROLE_ADMIN" ? "Admin" :
    role === "ROLE_TECHNICIAN" ? "Technicien" :
    "Réception";

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
