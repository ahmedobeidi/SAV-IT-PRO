import { Link } from "react-router-dom";
import type { EquipmentBrandRead } from "../equipment.types";
import { Pencil, List, Trash2 } from "lucide-react";

export default function EquipmentBrandTable({
  items,
  typeId,
  onEdit,
  onDelete,
}: {
  items: EquipmentBrandRead[];
  typeId: number;
  onEdit: (b: EquipmentBrandRead) => void;
  onDelete: (b: EquipmentBrandRead) => void;
}) {
  return (
    <div className="card table-card">
      <table className="data-table">
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
          {items.map((b) => (
            <tr key={b.id}>
              <td
                className="data-table-cell data-table-cell--truncate"
                title={b.name}
              >
                <Link
                  to={`/admin/equipment/types/${typeId}/brands/${b.id}/models`}
                  className="link-primary"
                >
                  {b.name}
                </Link>
              </td>

              <td
                className="data-table-cell data-table-cell--actions"
              >
                <div
                  style={{
                    display: "flex",
                    gap: 8,
                    flexWrap: "wrap",
                    justifyContent: "center",
                  }}
                >
                  <Link
                    className="btn hover-bg-primary"
                    to={`/admin/equipment/types/${typeId}/brands/${b.id}/models`}
                    title="Modèles"
                    aria-label="Modèles"
                  >
                    <List size={18} />
                  </Link>

                  <button
                    className="btn hover-bg-primary"
                    onClick={() => onEdit(b)}
                    title="Renommer"
                    aria-label="Renommer"
                  >
                    <Pencil size={18} />
                  </button>

                  <button
                    className="btn hover-bg-danger"
                    onClick={() => onDelete(b)}
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
          Aucune marque.
        </div>
      )}
    </div>
  );
}
