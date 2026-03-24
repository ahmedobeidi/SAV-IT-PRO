import { Loader2 } from "lucide-react";
import { useGlobalLoading } from "./useGlobalLoading";

export function GlobalLoadingOverlay() {
  const { isLoading } = useGlobalLoading();
  if (!isLoading) return null;

  return (
    <div
      aria-live="polite"
      aria-busy="true"
      style={{
        position: "fixed",
        inset: 0,
        zIndex: 9999,
        background: "var(--overlay-soft)",
        backdropFilter: "blur(3px)",
        display: "grid",
        placeItems: "center",
        pointerEvents: "auto",
      }}
    >
      <div className="card loading-card">
        <Loader2 className="spin" size={18} />
        <div className="small">Chargement...</div>
      </div>
    </div>
  );
}
