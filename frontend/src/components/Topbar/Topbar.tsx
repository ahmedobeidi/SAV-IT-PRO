import { useNavigate } from "react-router-dom";
import { LogOut } from "lucide-react";
import { authStore } from "../../features/auth/auth.store";
import { authService } from "../../features/auth/auth.service";
import "./Topbar.css";

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
    <header className="topbar">
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