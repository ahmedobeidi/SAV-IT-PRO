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
          <col style={{ width: "85%" }} />
          <col style={{ width: "15%" }} />
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
          {items.map((t) => (
            <tr key={t.id}>
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
                title={t.name}
              >
                {/* keep same “link feel” as clients */}
                <Link
                  to={`/admin/equipment/types/${t.id}/brands`}
                  style={{ color: "var(--primary)" }}
                >
                  {t.name}
                </Link>
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

                <div className="small" style={{ marginTop: 6 }} />
              </td>
            </tr>
          ))}
        </tbody>
      </table>

      {items.length === 0 && (
        <div className="small" style={{ padding: 12 }}>
          Aucun type.
        </div>
      )}
    </div>
  );
}