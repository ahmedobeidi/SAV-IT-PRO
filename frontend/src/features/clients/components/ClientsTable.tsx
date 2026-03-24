import { Link } from "react-router-dom";
import type { ClientRead } from "../clients.types";
import { Pencil, UserX } from "lucide-react";

export default function ClientsTable({
  items,
  onAnonymize,
}: {
  items: ClientRead[];
  onAnonymize: (c: ClientRead) => void;
}) {
  return (
    <div className="card table-card">
      <table className="data-table">
        {/* ✅ fixed widths + “same width feel” */}
        <colgroup>
          <col style={{ width: "26%" }} />
          <col style={{ width: "18%" }} />
          <col style={{ width: "26%" }} />
          <col style={{ width: "14%" }} />
          <col style={{ width: "16%" }} />
        </colgroup>

        <thead>
          <tr className="data-table-head-row">
            {["Nom", "Téléphone", "Email", "Ville", "Actions"].map((h) => (
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
          {items.map((c) => (
            <tr key={c.id}>
              {/* Nom */}
              <td
                className="data-table-cell data-table-cell--truncate"
                title={`${c.lastName} ${c.firstName}`}
              >
                <Link
                  to={`/admin/clients/${c.id}/edit`} className="link-primary">
                  {c.lastName} {c.firstName}
                </Link>
                {c.isAnonymized && <span className="small"> — anonymisé</span>}
              </td>

              {/* Téléphone */}
              <td
                className="data-table-cell data-table-cell--truncate"
                title={c.phone}
              >
                <span className="small">{c.phone}</span>
              </td>

              {/* Email */}
              <td
                className="data-table-cell data-table-cell--truncate"
                title={c.email ?? ""}
              >
                <span className="small">{c.email ?? "-"}</span>
              </td>

              {/* Ville */}
              <td
                className="data-table-cell data-table-cell--truncate"
                title={c.city ?? ""}
              >
                <span className="small">{c.city ?? "-"}</span>
              </td>

              {/* Actions */}
              <td
                className="data-table-cell data-table-cell--actions"
              >
                <div className="table-actions">
                  {/* Edit */}
                  <Link
                    className="btn hover-bg-primary"
                    to={`/admin/clients/${c.id}/edit`}
                    title="Modifier"
                    aria-label="Modifier"
                  >
                    <Pencil size={18} />
                  </Link>

                  {/* Anonymize */}
                  <button
                    className="btn hover-bg-danger"
                    onClick={() => onAnonymize(c)}
                    disabled={c.isAnonymized}
                    title={c.isAnonymized ? "Déjà anonymisé" : "Anonymiser"}
                    aria-label="Anonymiser"
                  >
                    <UserX size={18} />
                  </button>
                </div>

                <div className="small mt-6" />
              </td>
            </tr>
          ))}
        </tbody>
      </table>

      {items.length === 0 && (
        <div className="small empty-state">
          Aucun client.
        </div>
      )}
    </div>
  );
}
