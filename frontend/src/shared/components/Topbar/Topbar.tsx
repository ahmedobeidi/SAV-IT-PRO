import { useNavigate } from "react-router-dom";
import { LogOut } from "lucide-react";
import { authStore } from "../../../features/auth/auth.store";
import { authService } from "../../../features/auth/auth.service";
import { getRoleLabel } from "../../../features/auth/auth.roles";
import { useAuth } from "../../../features/auth/useAuth";
import "./Topbar.css";

export default function Topbar() {
  const navigate = useNavigate();
  const { role, refreshToken } = useAuth();

  async function handleLogout() {
    try {
      if (refreshToken) {
        await authService.logout(refreshToken);
      }
    } catch {
      // ignore API logout error
    } finally {
      authStore.clear();
      navigate("/login", { replace: true });
    }
  }

  return (
    <header className="topbar">
      <div className="topbar-title">
        {getRoleLabel(role)}
      </div>

      <button className="logout-btn" onClick={handleLogout}>
        <LogOut size={16} strokeWidth={2.2} />
        <span>Déconnexion</span>
      </button>
    </header>
  );
}
