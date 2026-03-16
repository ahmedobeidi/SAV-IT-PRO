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
          <col style={{ width: "26%" }} />
          <col style={{ width: "18%" }} />
          <col style={{ width: "26%" }} />
          <col style={{ width: "14%" }} />
          <col style={{ width: "16%" }} />
        </colgroup>

        <thead>
          <tr style={{ textAlign: "left" }}>
            {["Nom", "Téléphone", "Email", "Ville", "Actions"].map((h) => (
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
          {items.map((c) => (
            <tr key={c.id}>
              {/* Nom */}
              <td
                style={{
                  padding: "10px 8px",
                  borderBottom: "1px solid var(--border)",
                  verticalAlign: "top",
                  whiteSpace: "nowrap",
                  overflow: "hidden",
                  textOverflow: "ellipsis",
                }}
                title={`${c.lastName} ${c.firstName}`}
              >
                <div style={{ color: "var(--primary)" }}>
                  {c.lastName} {c.firstName}
                </div>
                {c.isAnonymized && <span className="small"> — anonymisé</span>}
              </td>

              {/* Téléphone */}
              <td
                style={{
                  padding: "10px 8px",
                  borderBottom: "1px solid var(--border)",
                  verticalAlign: "top",
                  whiteSpace: "nowrap",
                  overflow: "hidden",
                  textOverflow: "ellipsis",
                }}
                title={c.phone}
              >
                <span className="small">{c.phone}</span>
              </td>

              {/* Email */}
              <td
                style={{
                  padding: "10px 8px",
                  borderBottom: "1px solid var(--border)",
                  verticalAlign: "top",
                  whiteSpace: "nowrap",
                  overflow: "hidden",
                  textOverflow: "ellipsis",
                }}
                title={c.email ?? ""}
              >
                <span className="small">{c.email ?? "-"}</span>
              </td>

              {/* Ville */}
              <td
                style={{
                  padding: "10px 8px",
                  borderBottom: "1px solid var(--border)",
                  verticalAlign: "top",
                  whiteSpace: "nowrap",
                  overflow: "hidden",
                  textOverflow: "ellipsis",
                }}
                title={c.city ?? ""}
              >
                <span className="small">{c.city ?? "-"}</span>
              </td>

              {/* Actions */}
              <td
                style={{
                  padding: "10px 8px",
                  borderBottom: "1px solid var(--border)",
                  verticalAlign: "top",
                  textAlign: "center",
                }}
              >
                <div style={{ display: "flex", gap: 8, flexWrap: "wrap", justifyContent: "center", }}>
                  {/* Edit */}
                  <Link
                    className="btn"
                    to={`/admin/clients/${c.id}/edit`}
                    title="Modifier"
                    aria-label="Modifier"
                  >
                    <Pencil size={18} />
                  </Link>

                  {/* Anonymize */}
                  <button
                    className="btn"
                    onClick={() => onAnonymize(c)}
                    disabled={c.isAnonymized}
                    title={c.isAnonymized ? "Déjà anonymisé" : "Anonymiser"}
                    aria-label="Anonymiser"
                  >
                    <UserX size={18} />
                  </button>
                </div>

                <div className="small" style={{ marginTop: 6 }} />
              </td>
            </tr>
          ))}
        </tbody>
      </table>

      {items.length === 0 && (
        <div className="small" style={{ padding: 12 }}>
          Aucun client.
        </div>
      )}
    </div>
  );
}