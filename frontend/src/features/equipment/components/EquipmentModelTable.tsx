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
    <div className="card table-card">
      <table className="data-table">
        {/* ✅ fixed widths like ClientsTable */}
        <colgroup>
          <col style={{ width: "85%" }} />
          <col style={{ width: "15%" }} />
        </colgroup>

        <thead>
          <tr className="data-table-head-row">
            {["Nom", "Actions"].map((h) => (
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
          {items.map((m) => (
            <tr key={m.id}>
              {/* Nom */}
              <td
                className="data-table-cell data-table-cell--truncate"
                title={m.name}
              >
                <span style={{ color: "var(--primary)", cursor: "pointer" }} onClick={() => onEdit(m)}>{m.name}</span>
              </td>

              {/* Actions */}
              <td
                className="data-table-cell data-table-cell--actions"
              >
                <div className="table-actions">
                  {/* Rename */}
                  <button
                    className="btn hover-bg-primary"
                    onClick={() => onEdit(m)}
                    title="Renommer"
                    aria-label="Renommer"
                  >
                    <Pencil size={18} />
                  </button>

                  {/* Delete */}
                  <button
                    className="btn hover-bg-danger"
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
        <div className="small empty-state">
          Aucun modèle.
        </div>
      )}
    </div>
  );
}
