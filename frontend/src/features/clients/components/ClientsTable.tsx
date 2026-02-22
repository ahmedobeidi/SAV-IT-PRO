import { Link } from "react-router-dom";
import type { ClientRead } from "../clients.types";

export default function ClientsTable({
  items,
  onAnonymize,
}: {
  items: ClientRead[];
  onAnonymize: (c: ClientRead) => void;
}) {
  return (
    <div className="card" style={{ padding: 12, overflowX: "auto" }}>
      <table style={{ width: "100%", borderCollapse: "collapse" }}>
        <thead>
          <tr style={{ textAlign: "left" }}>
            {["Nom", "Téléphone", "Email", "Ville", "Actions"].map((h) => (
              <th key={h} style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)" }}>
                <span className="small">{h}</span>
              </th>
            ))}
          </tr>
        </thead>

        <tbody>
          {items.map((c) => (
            <tr key={c.id}>
              <td style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)" }}>
                <Link to={`/admin/clients/${c.id}`} style={{ color: "var(--primary)" }}>
                  {c.lastName} {c.firstName}
                </Link>
                {c.isAnonymized && <span className="small"> — anonymisé</span>}
              </td>

              <td style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)" }}>
                <span className="small">{c.phone}</span>
              </td>

              <td style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)" }}>
                <span className="small">{c.email ?? "-"}</span>
              </td>

              <td style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)" }}>
                <span className="small">{c.city ?? "-"}</span>
              </td>

              <td style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)" }}>
                <div style={{ display: "flex", gap: 8, flexWrap: "wrap" }}>
                  <Link className="btn" to={`/admin/clients/${c.id}/edit`}>
                    Modifier
                  </Link>
                  <Link className="btn" to={`/admin/clients/${c.id}/repairs`}>
                    Réparations
                  </Link>
                  <button className="btn btn-danger" onClick={() => onAnonymize(c)} disabled={c.isAnonymized}>
                    Anonymiser
                  </button>
                </div>
              </td>
            </tr>
          ))}
        </tbody>
      </table>

      {items.length === 0 && <div className="small" style={{ padding: 12 }}>Aucun client.</div>}
    </div>
  );
}