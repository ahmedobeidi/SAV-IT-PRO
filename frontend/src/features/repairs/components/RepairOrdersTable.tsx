import RepairStatusBadge from "./RepairStatusBadge";
import type { RepairOrderRead } from "../repairs.types";
import { Eye, UserPlus, RefreshCw, Pencil } from "lucide-react";
import { useTicketActions } from "../hooks/useTicketActions";

const headCellStyle: React.CSSProperties = {
  padding: "16px 12px",
  borderBottom: "1px solid var(--border)",
  textAlign: "left",
};

const cellStyle: React.CSSProperties = {
  padding: "20px 12px",
  borderBottom: "1px solid var(--border)",
  verticalAlign: "middle",
};

type Mode = "staff" | "tech";

function RepairRow({
  r,
  mode,
  onEdit,
  onAssign,
  onUpdateStatus,
  onRefresh,
  onMessage,
}: {
  r: RepairOrderRead;
  mode: Mode;
  onEdit?: (repair: RepairOrderRead) => void;
  onAssign?: (repair: RepairOrderRead) => void;
  onUpdateStatus: (repair: RepairOrderRead) => void;
  onRefresh: () => void;
  onMessage?: (type: "success" | "error", text: string) => void;
}) {
  const { openTicket, loadingTicket } = useTicketActions(
    r.id,
    onRefresh,
    onMessage ?? (() => {}),
  );

  return (
    <tr>
      <td style={cellStyle}>
        <div style={{ color: "var(--primary)" }}>{r.reference}</div>
        <div className="small">
          {r.createdAt ? new Date(r.createdAt).toLocaleString("fr-FR") : ""}
        </div>
      </td>

      <td style={cellStyle}>
        <div>
          {r.createdFor.lastName} {r.createdFor.firstName}
        </div>
        <div className="small">{r.createdFor.phone}</div>
      </td>

      <td style={cellStyle}>
        <div className="small">{r.equipmentModel?.name ?? "-"}</div>
      </td>

      <td style={cellStyle}>
        <div className="small">{r.issue?.name ?? "-"}</div>
      </td>

      <td style={cellStyle}>
        <RepairStatusBadge status={r.status} />
      </td>

      <td style={cellStyle}>
        <span className="small">
          {r.assignedTo
            ? `${r.assignedTo.lastName ?? ""} ${r.assignedTo.firstName}`
            : "-"}
        </span>
      </td>

      <td
        style={{
          ...cellStyle,
          textAlign: "center",
        }}
      >
        <div
          style={{
            display: "flex",
            gap: 10,
            flexWrap: "wrap",
            alignItems: "center",
            justifyContent: "center",
          }}
        >
          {mode === "staff" && onEdit && (
            <button
              className="btn hover-bg-primary"
              onClick={() => onEdit(r)}
              title="Modifier"
              aria-label="Modifier"
            >
              <Pencil size={18} />
            </button>
          )}

          {mode === "staff" && (
            <button
              className="btn hover-bg-primary"
              onClick={openTicket}
              title="Voir le ticket"
              aria-label="Voir le ticket"
              disabled={loadingTicket}
              style={{
                opacity: loadingTicket ? 0.6 : 1,
                cursor: loadingTicket ? "wait" : "pointer",
              }}
            >
              <Eye size={18} />
            </button>
          )}

          {mode === "staff" && onAssign && (
            <button
              className="btn hover-bg-primary"
              onClick={() => onAssign(r)}
              title="Affecter"
              aria-label="Affecter"
            >
              <UserPlus size={18} />
            </button>
          )}

          <button
            className="btn hover-bg-primary"
            onClick={() => onUpdateStatus(r)}
            title="Statut"
            aria-label="Statut"
          >
            <RefreshCw size={18} />
          </button>
        </div>
      </td>
    </tr>
  );
}

export default function RepairOrdersTable({
  items,
  mode = "staff",
  onEdit,
  onAssign,
  onUpdateStatus,
  onRefresh,
  onMessage,
}: {
  items: RepairOrderRead[];
  mode?: Mode;
  onEdit?: (repair: RepairOrderRead) => void;
  onAssign?: (repair: RepairOrderRead) => void;
  onUpdateStatus: (repair: RepairOrderRead) => void;
  onRefresh: () => void;
  onMessage?: (type: "success" | "error", text: string) => void;
}) {
  return (
    <div className="card" style={{ padding: 12, overflowX: "auto" }}>
      <table style={{ width: "100%", borderCollapse: "collapse" }}>
        <thead>
          <tr>
            {[
              "Référence",
              "Client",
              "Équipement",
              "Panne",
              "Statut",
              "Technicien",
              "Actions",
            ].map((h) => (
              <th
                key={h}
                style={{
                  ...headCellStyle,
                  textAlign: h === "Actions" ? "center" : "left",
                }}
              >
                <span className="small">{h}</span>
              </th>
            ))}
          </tr>
        </thead>

        <tbody>
          {items.map((r) => (
            <RepairRow
              key={r.id}
              r={r}
              mode={mode}
              onEdit={onEdit}
              onAssign={onAssign}
              onUpdateStatus={onUpdateStatus}
              onRefresh={onRefresh}
              onMessage={onMessage}
            />
          ))}
        </tbody>
      </table>

      {items.length === 0 && (
        <div className="small" style={{ padding: 12 }}>
          Aucune réparation.
        </div>
      )}
    </div>
  );
}