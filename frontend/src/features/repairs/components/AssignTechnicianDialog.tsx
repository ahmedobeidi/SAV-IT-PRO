import { useState, useEffect } from "react";
import { useUsersList } from "../../users/hooks/useUsersList";

export default function AssignTechnicianDialog({
  open,
  onClose,
  onConfirm,
}: {
  open: boolean;
  onClose: () => void;
  onConfirm: (technicianId: number | null) => Promise<void>;
}) {
  const [technicianId, setTechnicianId] = useState("");
  const [loading, setLoading] = useState(false);

  // Fetch all technicians
  const { data: usersData, loading: usersLoading } = useUsersList("", 1, 1000);
  const technicians =
    usersData?.items.filter((u) => u.role === "ROLE_TECHNICIAN") || [];

  // Reset selection when dialog opens
  useEffect(() => {
    if (open) {
      setTechnicianId("");
    }
  }, [open]);

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
      <div
        className="card"
        style={{ width: "100%", maxWidth: 520, padding: 16 }}
      >
        <div style={{ fontWeight: 700, marginBottom: 6 }}>
          Affecter un technicien
        </div>
        <div className="small" style={{ marginBottom: 10 }}>
          Sélectionnez un technicien dans la liste.
        </div>

        <select
          className="input"
          value={technicianId}
          onChange={(e) => setTechnicianId(e.target.value)}
          disabled={usersLoading}
        >
          <option value="">-- Aucun technicien --</option>
          {technicians.map((tech) => (
            <option key={tech.id} value={tech.id}>
              {tech.lastName} {tech.firstName}
            </option>
          ))}
        </select>

        <div
          style={{
            display: "flex",
            gap: 10,
            justifyContent: "end",
            marginTop: 12,
          }}
        >
          <button className="btn" onClick={onClose}>
            Annuler
          </button>

          <button
            className="btn btn-primary"
            disabled={loading}
            onClick={async () => {
              const id = technicianId === "" ? null : Number(technicianId);
              setLoading(true);
              try {
                await onConfirm(id as any);
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
