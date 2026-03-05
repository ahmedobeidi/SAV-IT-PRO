import type { EquipmentModelRead } from "../equipment.types";
import { Pencil, Trash2 } from "lucide-react";

export default function EquipmentModelTable({
  items,
  onEdit,
  onDelete,
}: {
  items: EquipmentModelRead[];
  onEdit: (m: EquipmentModelRead) => void;
  onDelete: (m: EquipmentModelRead) => void;
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
        {/* ✅ fixed widths like ClientsTable */}
        <colgroup>
          <col style={{ width: "70%" }} />
          <col style={{ width: "30%" }} />
        </colgroup>

        <thead>
          <tr style={{ textAlign: "left" }}>
            {["Nom", "Actions"].map((h) => (
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
          {items.map((m) => (
            <tr key={m.id}>
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
                title={m.name}
              >
                <span style={{ fontWeight: 600 }}>{m.name}</span>
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
                  {/* Rename */}
                  <button
                    className="btn btn-warning"
                    onClick={() => onEdit(m)}
                    title="Renommer"
                    aria-label="Renommer"
                  >
                    <Pencil size={18} />
                  </button>

                  {/* Delete */}
                  <button
                    className="btn btn-danger"
                    onClick={() => onDelete(m)}
                    title="Supprimer"
                    aria-label="Supprimer"
                  >
                    <Trash2 size={18} />
                  </button>
                </div>
              </td>
            </tr>
          ))}
        </tbody>
      </table>

      {items.length === 0 && (
        <div className="small" style={{ padding: 12 }}>
          Aucun modèle.
        </div>
      )}
    </div>
  );
}