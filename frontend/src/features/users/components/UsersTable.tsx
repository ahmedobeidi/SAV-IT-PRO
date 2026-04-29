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
    <div className="card table-card">
      <table className="data-table">
        {/* ✅ fixed widths + “same width feel” */}
        <colgroup>
          <col style={{ width: "24%" }} />
          <col style={{ width: "38%" }} />
          <col style={{ width: "18%" }} />
          <col style={{ width: "12%" }} />
          <col style={{ width: "16%" }} />
        </colgroup>

        <thead>
          <tr className="data-table-head-row">
            {["Nom", "Email", "Rôle", "Statut", "Actions"].map((h) => (
              <th
                key={h}
                className={`data-table-head-cell ${h === "Actions" ? "data-table-head-cell--actions" : ""}`}
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
                className="data-table-cell data-table-cell--truncate"
                title={`${u.lastName} ${u.firstName}`}
              >
                <Link
                  to={`/admin/users/${u.id}/edit`} className="link-primary">
                  {u.lastName} {u.firstName}
                </Link>
                {u.isAnonymized && <span className="small"> — anonymisé</span>}
              </td>

              <td
                className="data-table-cell data-table-cell--truncate"
              >
                <span className="small">{u.email}</span>
              </td>

              <td className="data-table-cell">
                <RoleBadge role={u.role} />
              </td>

              <td className="data-table-cell">
                <span className={`status-text ${u.isActive ? "status-text-success" : "status-text-danger"}`}>
                  {u.isActive ? "Actif" : "Bloqué"}
                </span>
              </td>

              <td className="data-table-cell data-table-cell--actions">
                <div className="table-actions">
                  {/* Edit */}
                  <Link className="btn hover-bg-primary" to={`/admin/users/${u.id}/edit`} title="Modifier" aria-label="Modifier">
                    <Pencil size={18} />
                  </Link>

                  {/* Block / Unblock */}
                  <button
                    className="btn hover-bg-primary"
                    onClick={() => onToggleActive(u)}
                    title={u.isActive ? "Bloquer" : "Débloquer"}
                    aria-label={u.isActive ? "Bloquer" : "Débloquer"}
                  >
                    {u.isActive ? <Ban size={18} /> : <CheckCircle2 size={18} />}
                  </button>

                  {/* Anonymize */}
                  <button
                    className="btn hover-bg-danger"
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
        <div className="small empty-state">
          Aucun employé.
        </div>
      )}
    </div>
  );
}
