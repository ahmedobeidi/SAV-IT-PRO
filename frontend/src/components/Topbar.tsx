import { useNavigate } from "react-router-dom";
import { authStore } from "../auth/auth.store";
import { authService } from "../auth/auth.service";

export default function Topbar() {
  const navigate = useNavigate();

  async function handleLogout() {
    const { refreshToken } = authStore.getTokens();
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
        backdropFilter: "blur(6px)",
      }}
    >
      <div style={{ fontWeight: 600 }}>Administration</div>

      <button className="btn btn-danger" onClick={handleLogout}>
        Déconnexion
      </button>
    </header>
  );
}
