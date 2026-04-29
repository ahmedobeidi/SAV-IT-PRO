import { useEffect, useState } from "react";
import type { RepairStatus } from "../repairs.types";
import { getStatusLabel } from "../utils/statusTranslations";

const ALL: RepairStatus[] = [
  "CREATED",
  "IN_PROGRESS",
  "WAITING_PARTS",
  "DONE",
  "DELIVERED",
  "CANCELED",
];

export default function UpdateStatusDialog({
  open,
  current,
  allowed,
  onClose,
  onConfirm,
}: {
  open: boolean;
  current: RepairStatus;
  allowed: RepairStatus[];
  onClose: () => void;
  onConfirm: (status: RepairStatus) => Promise<void>;
}) {
  const [status, setStatus] = useState<RepairStatus>(current);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (open) {
      setStatus(current);
      setLoading(false);
    }
  }, [open, current]);

  if (!open) return null;

  const options = allowed.length ? allowed : ALL;

  return (
    <div
      style={{
        position: "fixed",
        inset: 0,
        background: "var(--overlay)",
        display: "grid",
        placeItems: "center",
        padding: 16,
        zIndex: 60,
      }}
    >
      <div className="card" style={{ width: "100%", maxWidth: 520, padding: 16 }}>
        <div style={{ fontWeight: 700, marginBottom: 6 }}>Changer le statut</div>

        <select
          className="input"
          value={status}
          onChange={(e) => setStatus(e.target.value as RepairStatus)}
          disabled={loading}
        >
          {options.map((s) => (
            <option key={s} value={s}>
              {getStatusLabel(s)}
            </option>
          ))}
        </select>

        <div style={{ display: "flex", gap: 10, justifyContent: "end", marginTop: 12 }}>
          <button className="btn" onClick={onClose} disabled={loading}>
            Annuler
          </button>

          <button
            className="btn btn-primary"
            disabled={loading}
            onClick={async () => {
              setLoading(true);
              try {
                await onConfirm(status);
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
