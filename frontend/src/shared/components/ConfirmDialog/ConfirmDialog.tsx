import "./ConfirmDialog.css";

export default function ConfirmDialog({
  open,
  title,
  message,
  confirmText = "Confirmer",
  cancelText = "Annuler",
  danger,
  onConfirm,
  onCancel,
}: {
  open: boolean;
  title: string;
  message: string;
  confirmText?: string;
  cancelText?: string;
  danger?: boolean;
  onConfirm: () => void;
  onCancel: () => void;
}) {
  if (!open) return null;

  return (
    <div className="confirm-dialog-overlay">
      <div className="card confirm-dialog">
        <div className="confirm-dialog-title">{title}</div>
        <div className="small confirm-dialog-message">{message}</div>

        <div className="confirm-dialog-actions">
          <button className="btn" onClick={onCancel}>
            {cancelText}
          </button>
          <button
            className={`btn ${danger ? "btn-danger" : "btn-primary"}`}
            onClick={onConfirm}
          >
            {confirmText}
          </button>
        </div>
      </div>
    </div>
  );
}
