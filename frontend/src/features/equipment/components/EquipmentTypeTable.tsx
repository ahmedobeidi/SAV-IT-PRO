import { Link } from "react-router-dom";
import type { EquipmentTypeRead } from "../equipment.types";
import { Pencil, Tags, Trash2 } from "lucide-react";

export default function EquipmentTypeTable({
  items,
  onEdit,
  onDelete,
}: {
  items: EquipmentTypeRead[];
  onEdit: (t: EquipmentTypeRead) => void;
  onDelete: (t: EquipmentTypeRead) => void;
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
          {items.map((t) => (
            <tr key={t.id}>
              {/* Nom */}
              <td
                className="data-table-cell data-table-cell--truncate"
                title={t.name}
              >
                {/* keep same “link feel” as clients */}
                <Link
                  to={`/admin/equipment/types/${t.id}/brands`}
                  className="link-primary"
                >
                  {t.name}
                </Link>
              </td>

              {/* Actions */}
              <td
                className="data-table-cell data-table-cell--actions"
              >
                <div className="table-actions">
                  {/* Brands */}
                  <Link
                    className="btn hover-bg-primary"
                    to={`/admin/equipment/types/${t.id}/brands`}
                    title="Marques"
                    aria-label="Marques"
                  >
                    <Tags size={18} />
                  </Link>

                  {/* Rename */}
                  <button
                    className="btn hover-bg-primary"
                    onClick={() => onEdit(t)}
                    title="Renommer"
                    aria-label="Renommer"
                  >
                    <Pencil size={18} />
                  </button>

                  {/* Delete */}
                  <button
                    className="btn hover-bg-danger"
                    onClick={() => onDelete(t)}
                    title="Supprimer"
                    aria-label="Supprimer"
                  >
                    <Trash2 size={18} />
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
          Aucun type.
        </div>
      )}
    </div>
  );
}
