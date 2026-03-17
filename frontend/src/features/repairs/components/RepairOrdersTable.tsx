import RepairStatusBadge from "./RepairStatusBadge";
import type { RepairOrderRead } from "../repairs.types";
import { Eye, Send, UserPlus, RefreshCw } from "lucide-react";
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

function RepairRow({
  r,
  mode,
  onAssign,
  onUpdateStatus,
  onRefresh,
}: {
  r: RepairOrderRead;
  mode: "staff" | "tech";
  onAssign: (repair: RepairOrderRead) => void;
  onUpdateStatus: (repair: RepairOrderRead) => void;
  onRefresh: () => void;
}) {
  const { ticket, loadingTicket, msg, openTicket, send } = useTicketActions(
    r.id,
    onRefresh
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

      <td style={cellStyle}>
        {loadingTicket ? (
          <span className="small">Chargement...</span>
        ) : ticket ? (
          <span className="small" style={{ fontWeight: 600 }}>
            {ticket.isSent ? "Envoyé" : "Non envoyé"}
          </span>
        ) : (
          <span className="small">Aucun ticket</span>
        )}

        {msg && (
          <div className="small" style={{ marginTop: 6 }}>
            {msg}
          </div>
        )}
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
          <button
            className="btn"
            onClick={openTicket}
            disabled={!ticket}
            title="Ouvrir"
            aria-label="Ouvrir"
          >
            <Eye size={18} />
          </button>

          {mode === "staff" && (
            <button
              className="btn"
              onClick={() => onAssign(r)}
              title="Affecter"
              aria-label="Affecter"
            >
              <UserPlus size={18} />
            </button>
          )}

          <button
            className="btn"
            onClick={() => onUpdateStatus(r)}
            title="Statut"
            aria-label="Statut"
          >
            <RefreshCw size={18} />
          </button>

          {mode === "staff" && (
            <button
              className="btn"
              onClick={send}
              disabled={!ticket || ticket.isSent}
              title="Envoyer au client"
              aria-label="Envoyer au client"
            >
              <Send size={18} />
            </button>
          )}
        </div>
      </td>
    </tr>
  );
}

export default function RepairOrdersTable({
  items,
  mode,
  onAssign,
  onUpdateStatus,
  onRefresh,
}: {
  items: RepairOrderRead[];
  mode: "staff" | "tech";
  onAssign: (repair: RepairOrderRead) => void;
  onUpdateStatus: (repair: RepairOrderRead) => void;
  onRefresh: () => void;
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
              "Ticket",
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
              onAssign={onAssign}
              onUpdateStatus={onUpdateStatus}
              onRefresh={onRefresh}
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