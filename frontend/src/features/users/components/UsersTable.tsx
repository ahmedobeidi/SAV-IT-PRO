import { Link } from "react-router-dom";
import RoleBadge from "./RoleBadge";
import type { UserRead } from "../users.types";

export default function UsersTable({
  items,
  onToggleActive,
  onAnonymize,
}: {
  items: UserRead[];
  onToggleActive: (u: UserRead) => void;
  onAnonymize: (u: UserRead) => void;
}) {
  return (
    <div className="card" style={{ padding: 12, overflowX: "auto" }}>
      <table style={{ width: "100%", borderCollapse: "collapse" }}>
        <thead>
          <tr style={{ textAlign: "left" }}>
            {["Nom", "Email", "Rôle", "Statut", "Actions"].map((h) => (
              <th key={h} style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)" }}>
                <span className="small">{h}</span>
              </th>
            ))}
          </tr>
        </thead>

        <tbody>
          {items.map((u) => (
            <tr key={u.id}>
              <td style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)" }}>
                <Link to={`/admin/users/${u.id}`} style={{ color: "var(--primary)" }}>
                  {u.lastName} {u.firstName}
                </Link>
                {u.isAnonymized && <span className="small"> — anonymisé</span>}
              </td>

              <td style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)" }}>
                <span className="small">{u.email}</span>
              </td>

              <td style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)" }}>
                <RoleBadge role={u.role} />
              </td>

              <td style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)" }}>
                <span style={{ color: u.isActive ? "var(--success)" : "var(--danger)", fontSize: 13 }}>
                  {u.isActive ? "Actif" : "Bloqué"}
                </span>
              </td>

              <td style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)" }}>
                <div style={{ display: "flex", gap: 8, flexWrap: "wrap" }}>
                  <Link className="btn" to={`/admin/users/${u.id}/edit`}>
                    Modifier
                  </Link>
                  <button className="btn" onClick={() => onToggleActive(u)}>
                    {u.isActive ? "Bloquer" : "Débloquer"}
                  </button>
                  <button className="btn btn-danger" onClick={() => onAnonymize(u)} disabled={u.isAnonymized}>
                    Anonymiser
                  </button>
                </div>
              </td>
            </tr>
          ))}
        </tbody>
      </table>

      {items.length === 0 && <div className="small" style={{ padding: 12 }}>Aucun utilisateur.</div>}
    </div>
  );
}
