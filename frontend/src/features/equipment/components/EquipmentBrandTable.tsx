import { Link } from "react-router-dom";
import type { EquipmentBrandRead } from "../equipment.types";

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
          {items.map((b) => (
            <tr key={b.id}>
              <td style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)" }}>
                <div style={{ fontWeight: 600 }}>{b.name}</div>
                <div className="small">ID: {b.id}</div>
              </td>

              <td style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)" }}>
                <div style={{ display: "flex", gap: 8, flexWrap: "wrap" }}>
                  <Link className="btn" to={`/admin/equipment/brands/${b.id}/models`}>Modèles</Link>
                  <button className="btn" onClick={() => onEdit(b)}>Renommer</button>
                  <button className="btn btn-danger" onClick={() => onDelete(b)}>Supprimer</button>
                </div>
                <div className="small" style={{ marginTop: 6 }}>
                  (Suppression bloquée si des modèles existent → 409)
                </div>
              </td>
            </tr>
          ))}
        </tbody>
      </table>

      {items.length === 0 && <div className="small" style={{ padding: 12 }}>Aucune marque.</div>}
    </div>
  );
}