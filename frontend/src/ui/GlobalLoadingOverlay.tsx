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
        background: "rgba(0,0,0,0.35)",
        backdropFilter: "blur(3px)",
        display: "grid",
        placeItems: "center",
        pointerEvents: "auto",
      }}
    >
      <div className="card" style={{ padding: 16, display: "flex", gap: 10, alignItems: "center" }}>
        <Loader2 className="spin" size={18} />
        <div className="small" style={{ color: "var(--text)" }}>Chargement...</div>
      </div>
    </div>
  );
}
