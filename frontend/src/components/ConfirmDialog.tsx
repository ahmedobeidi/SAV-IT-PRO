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
    <div
      style={{
        position: "fixed",
        inset: 0,
        zIndex: 9999,

        // 👇 same logic as GlobalLoadingOverlay
        background: "rgba(0,0,0,0.35)",
        backdropFilter: "blur(4px)",
        WebkitBackdropFilter: "blur(4px)", // ✅ Safari support

        display: "grid",
        placeItems: "center",
        padding: 16,
        pointerEvents: "auto",
      }}
    >
      <div
        className="card"
        style={{ width: "100%", maxWidth: 520, padding: 16 }}
      >
        <div style={{ fontWeight: 700, marginBottom: 6 }}>{title}</div>
        <div className="small" style={{ marginBottom: 14 }}>
          {message}
        </div>

        <div style={{ display: "flex", gap: 10, justifyContent: "end" }}>
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
