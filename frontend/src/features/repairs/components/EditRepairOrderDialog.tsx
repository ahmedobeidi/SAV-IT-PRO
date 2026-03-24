import "./EditRepairOrderDialog.css";
import type { RepairOrderRead, UpdateRepairOrderPayload } from "../repairs.types";
import RepairOrderEditForm from "./RepairOrderEditForm";

export default function EditRepairOrderDialog({
  open,
  repair,
  onClose,
  onSubmit,
}: {
  open: boolean;
  repair: RepairOrderRead | null;
  onClose: () => void;
  onSubmit: (payload: UpdateRepairOrderPayload) => Promise<void>;
}) {
  if (!open || !repair) return null;

  return (
    <div className="edit-repair-dialog-overlay" onClick={onClose}>
      <div
        className="card edit-repair-dialog"
        onClick={(e) => e.stopPropagation()}
      >
        <div className="edit-repair-dialog-header">
          <h3 style={{ margin: 0 }}>Modifier la réparation</h3>

          <button className="btn" onClick={onClose}>
            Fermer
          </button>
        </div>

        <RepairOrderEditForm repair={repair} onSubmit={onSubmit} />
      </div>
    </div>
  );
}
