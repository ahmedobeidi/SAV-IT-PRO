import { Link } from "react-router-dom";
import RoleBadge from "./RoleBadge";
import type { UserRead } from "../users.types";
import { Pencil, Ban, CheckCircle2, UserX } from "lucide-react";

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
      <table
        style={{
          width: "100%",
          borderCollapse: "collapse",
          tableLayout: "fixed",
        }}
      >
        {/* ✅ fixed widths + “same width feel” */}
        <colgroup>
          <col style={{ width: "24%" }} />
          <col style={{ width: "38%" }} />
          <col style={{ width: "18%" }} />
          <col style={{ width: "12%" }} />
          <col style={{ width: "16%" }} />
        </colgroup>

        <thead>
          <tr style={{ textAlign: "left" }}>
            {["Nom", "Email", "Rôle", "Statut", "Actions"].map((h) => (
              <th
                key={h}
                style={{
                  padding: "10px 8px",
                  borderBottom: "1px solid var(--border)",
                  textAlign: h === "Actions" ? "center" : "left",
                }}
              >
                <span className="small">{h}</span>
              </th>
            ))}
          </tr>
        </thead>

        <tbody>
          {items.map((u) => (
            <tr key={u.id}>
              <td
                style={{
                  padding: "10px 8px",
                  borderBottom: "1px solid var(--border)",
                  verticalAlign: "top",
                  whiteSpace: "nowrap",
                  overflow: "hidden",
                  textOverflow: "ellipsis",
                }}
                title={`${u.lastName} ${u.firstName}`}
              >
                <div style={{ color: "var(--primary)" }}>
                  {u.lastName} {u.firstName}
                </div>
                {u.isAnonymized && <span className="small"> — anonymisé</span>}
              </td>

              <td
                style={{
                  padding: "10px 8px",
                  borderBottom: "1px solid var(--border)",
                  verticalAlign: "top",
                  whiteSpace: "nowrap",
                  overflow: "hidden",
                  textOverflow: "ellipsis",
                }}
              >
                <span className="small">{u.email}</span>
              </td>

              <td style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)", verticalAlign: "top" }}>
                <RoleBadge role={u.role} />
              </td>

              <td style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)", verticalAlign: "top" }}>
                <span style={{ color: u.isActive ? "var(--success)" : "var(--danger)", fontSize: 13 }}>
                  {u.isActive ? "Actif" : "Bloqué"}
                </span>
              </td>

              <td style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)", verticalAlign: "top" }}>
                <div style={{ display: "flex", gap: 8, flexWrap: "wrap" }}>
                  {/* Edit */}
                  <Link className="btn" to={`/admin/users/${u.id}/edit`} title="Modifier" aria-label="Modifier">
                    <Pencil size={18} />
                  </Link>

                  {/* Block / Unblock */}
                  <button
                    className="btn"
                    onClick={() => onToggleActive(u)}
                    title={u.isActive ? "Bloquer" : "Débloquer"}
                    aria-label={u.isActive ? "Bloquer" : "Débloquer"}
                  >
                    {u.isActive ? <Ban size={18} /> : <CheckCircle2 size={18} />}
                  </button>

                  {/* Anonymize */}
                  <button
                    className="btn"
                    onClick={() => onAnonymize(u)}
                    disabled={u.isAnonymized}
                    title={u.isAnonymized ? "Déjà anonymisé" : "Anonymiser"}
                    aria-label="Anonymiser"
                  >
                    <UserX size={18} />
                  </button>
                </div>

                <div className="small" style={{ marginTop: 6 }}>
                </div>
              </td>
            </tr>
          ))}
        </tbody>
      </table>

      {items.length === 0 && (
        <div className="small" style={{ padding: 12 }}>
          Aucun employé.
        </div>
      )}
    </div>
  );
}
