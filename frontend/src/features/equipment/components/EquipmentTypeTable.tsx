import { Link } from "react-router-dom";
import type { EquipmentTypeRead } from "../equipment.types";

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
      <table style={{ width: "100%", borderCollapse: "collapse" }}>
        <thead>
          <tr style={{ textAlign: "left" }}>
            {["Nom", "Actions"].map((h) => (
              <th key={h} style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)" }}>
                <span className="small">{h}</span>
              </th>
            ))}
          </tr>
        </thead>

        <tbody>
          {items.map((t) => (
            <tr key={t.id}>
              <td style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)" }}>
                <div style={{ fontWeight: 600 }}>{t.name}</div>
                <div className="small">ID: {t.id}</div>
              </td>

              <td style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)" }}>
                <div style={{ display: "flex", gap: 8, flexWrap: "wrap" }}>
                  <Link className="btn" to={`/admin/equipment/types/${t.id}/brands`}>Marques</Link>
                  <button className="btn" onClick={() => onEdit(t)}>Renommer</button>
                  <button className="btn btn-danger" onClick={() => onDelete(t)}>Supprimer</button>
                </div>
                <div className="small" style={{ marginTop: 6 }}>
                  (Suppression bloquée si des marques existent → 409)
                </div>
              </td>
            </tr>
          ))}
        </tbody>
      </table>

      {items.length === 0 && <div className="small" style={{ padding: 12 }}>Aucun type.</div>}
    </div>
  );
}