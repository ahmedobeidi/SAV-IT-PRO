import { useState } from "react";

export default function AssignTechnicianDialog({
  open,
  onClose,
  onConfirm,
}: {
  open: boolean;
  onClose: () => void;
  onConfirm: (technicianId: number) => Promise<void>;
}) {
  const [technicianId, setTechnicianId] = useState("");
  const [loading, setLoading] = useState(false);

  if (!open) return null;

  return (
    <div
      style={{
        position: "fixed",
        inset: 0,
        background: "rgba(0,0,0,0.55)",
        display: "grid",
        placeItems: "center",
        padding: 16,
        zIndex: 60,
      }}
    >
      <div className="card" style={{ width: "100%", maxWidth: 520, padding: 16 }}>
        <div style={{ fontWeight: 700, marginBottom: 6 }}>Affecter un technicien</div>
        <div className="small" style={{ marginBottom: 10 }}>
          Temporaire: saisis l’ID du technicien.
        </div>

        <input
          className="input"
          placeholder="Technician ID"
          value={technicianId}
          onChange={(e) => setTechnicianId(e.target.value)}
        />

        <div style={{ display: "flex", gap: 10, justifyContent: "end", marginTop: 12 }}>
          <button className="btn" onClick={onClose}>Annuler</button>
          <button
            className="btn btn-primary"
            disabled={loading}
            onClick={async () => {
              const id = Number(technicianId);
              if (!id) return;
              setLoading(true);
              try {
                await onConfirm(id);
                onClose();
              } finally {
                setLoading(false);
              }
            }}
          >
            {loading ? "..." : "Confirmer"}
          </button>
        </div>
      </div>
    </div>
  );
}