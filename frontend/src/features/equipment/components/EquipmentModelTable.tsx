import type { EquipmentModelRead } from "../equipment.types";

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
          {items.map((m) => (
            <tr key={m.id}>
              <td style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)" }}>
                <div style={{ fontWeight: 600 }}>{m.name}</div>
                <div className="small">ID: {m.id}</div>
              </td>

              <td style={{ padding: "10px 8px", borderBottom: "1px solid var(--border)" }}>
                <div style={{ display: "flex", gap: 8, flexWrap: "wrap" }}>
                  <button className="btn" onClick={() => onEdit(m)}>Renommer</button>
                  <button className="btn btn-danger" onClick={() => onDelete(m)}>Supprimer</button>
                </div>
                <div className="small" style={{ marginTop: 6 }}>
                  (Suppression bloquée si utilisé dans des réparations → 409)
                </div>
              </td>
            </tr>
          ))}
        </tbody>
      </table>

      {items.length === 0 && <div className="small" style={{ padding: 12 }}>Aucun modèle.</div>}
    </div>
  );
}