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
    <div className="card" style={{ padding: 12, overflowX: "auto" }}>
      <table
        style={{
          width: "100%",
          borderCollapse: "collapse",
          tableLayout: "fixed",
        }}
      >
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
          {items.map((b) => (
            <tr key={b.id}>
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
                <Link
                  to={`/admin/equipment/types/${typeId}/brands/${b.id}/models`}
                  style={{ color: "var(--primary)" }}
                >
                  {b.name}
                </Link>
              </td>

              <td
                style={{
                  padding: "10px 8px",
                  borderBottom: "1px solid var(--border)",
                  verticalAlign: "top",
                  textAlign: "center",
                }}
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
        <div className="small" style={{ padding: 12 }}>
          Aucune marque.
        </div>
      )}
    </div>
  );
}