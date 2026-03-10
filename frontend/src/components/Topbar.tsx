import { useNavigate } from "react-router-dom";
import { LogOut } from "lucide-react";
import { authStore } from "../auth/auth.store";
import { authService } from "../auth/auth.service";

export default function Topbar() {
  const navigate = useNavigate();
  const { role, refreshToken } = authStore.getTokens();

  async function handleLogout() {
    try {
      if (refreshToken) {
        await authService.logout(refreshToken);
      }
    } finally {
      authStore.clear();
      navigate("/login");
    }
  }

  return (
    <header
      style={{
        height: "var(--topbar-height)",
        display: "flex",
        alignItems: "center",
        justifyContent: "space-between",
        padding: "0 20px",
        borderBottom: "1px solid var(--border)",
        background: "rgba(43,43,43,0.7)",
        backdropFilter: "blur(10px)",
      }}
    >
      <div style={{ fontWeight: 600, letterSpacing: "0.2px" }}>
        {role ?? "Administration"}
      </div>

      <button className="logout-btn" onClick={handleLogout}>
        <LogOut size={16} strokeWidth={2.2} />
        <span>Déconnexion</span>
      </button>
    </header>
  );
}