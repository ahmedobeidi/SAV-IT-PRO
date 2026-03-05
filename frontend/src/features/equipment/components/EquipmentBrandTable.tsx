import { Link } from "react-router-dom";
import type { EquipmentBrandRead } from "../equipment.types";
import { Pencil, List, Trash2 } from "lucide-react";

export default function EquipmentBrandTable({
  items,
  onEdit,
  onDelete,
}: {
  items: EquipmentBrandRead[];
  onEdit: (b: EquipmentBrandRead) => void;
  onDelete: (b: EquipmentBrandRead) => void;
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
          {items.map((b) => (
            <tr key={b.id}>
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
                title={b.name}
              >
                {/* same “link feel” as clients */}
                <Link
                  to={`/admin/equipment/brands/${b.id}/models`}
                  style={{ color: "var(--primary)" }}
                >
                  {b.name}
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
                  {/* Models */}
                  <Link
                    className="btn"
                    to={`/admin/equipment/brands/${b.id}/models`}
                    title="Modèles"
                    aria-label="Modèles"
                  >
                    <List size={18} />
                  </Link>

                  {/* Rename */}
                  <button
                    className="btn"
                    onClick={() => onEdit(b)}
                    title="Renommer"
                    aria-label="Renommer"
                  >
                    <Pencil size={18} />
                  </button>

                  {/* Delete */}
                  <button
                    className="btn btn-danger"
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
        <div className="small" style={{ padding: 12 }}>
          Aucune marque.
        </div>
      )}
    </div>
  );
}