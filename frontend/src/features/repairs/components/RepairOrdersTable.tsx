import RepairStatusBadge from "./RepairStatusBadge";
import TicketActions from "./TicketActions";
import type { RepairOrderRead } from "../repairs.types";

export default function RepairOrdersTable({
  items,
  mode,
  onAssign,
  onUpdateStatus,
  onRefresh,
}: {
  items: RepairOrderRead[];
  mode: "staff" | "tech";
  onAssign: (repair: RepairOrderRead) => void; // staff only
  onUpdateStatus: (repair: RepairOrderRead) => void; // both
  onRefresh: () => void;
}) {
  return (
    <div className="card" style={{ padding: 12, overflowX: "auto" }}>
      <table style={{ width: "100%", borderCollapse: "collapse" }}>
        <thead>
          <tr style={{ textAlign: "left" }}>
            {["#", "Client", "Équipement", "Panne", "Statut", "Technicien", "Actions"].map((h) => (
              <th key={h} style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)" }}>
                <span className="small">{h}</span>
              </th>
            ))}
          </tr>
        </thead>

        <tbody>
          {items.map((r) => (
            <tr key={r.id}>
              <td style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)" }}>
                <div style={{ fontWeight: 700 }}>#{r.id}</div>
                <div className="small">
                  {r.createdAt
                    ? new Date(r.createdAt).toLocaleString("fr-FR")
                    : ""}
                </div>
              </td>

              <td style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)" }}>
                <div style={{ fontWeight: 600 }}>{r.createdFor.lastName} {r.createdFor.firstName}</div>
                <div className="small">{r.createdFor.phone}</div>
              </td>

              <td style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)" }}>
                <div style={{ fontWeight: 600 }}>{r.equipmentModel?.name ?? "-"}</div>
              </td>

              <td style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)" }}>
                <div className="small">{r.issue?.name ?? "-"}</div>
              </td>

              <td style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)" }}>
                <RepairStatusBadge status={r.status} />
              </td>

              <td style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)" }}>
                <span className="small">
                  {r.assignedTo ? `${r.assignedTo.lastName ?? ""} ${r.assignedTo.firstName}` : "-"}
                </span>
              </td>

              <td style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)" }}>
                <div style={{ display: "grid", gap: 10 }}>
                  <div style={{ display: "flex", gap: 8, flexWrap: "wrap" }}>
                    {mode === "staff" && (
                      <button className="btn" onClick={() => onAssign(r)}>
                        Affecter
                      </button>
                    )}
                    <button className="btn" onClick={() => onUpdateStatus(r)}>
                      Statut
                    </button>
                  </div>

                  {mode === "staff" && (
                    <TicketActions repairId={r.id} onDone={onRefresh} />
                  )}
                </div>
              </td>
            </tr>
          ))}
        </tbody>
      </table>

      {items.length === 0 && <div className="small" style={{ padding: 12 }}>Aucune réparation.</div>}
    </div>
  );
}