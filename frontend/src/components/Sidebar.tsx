import { NavLink } from "react-router-dom";
import { authStore } from "../auth/auth.store.ts";

const linkStyle = ({ isActive }: { isActive: boolean }) => ({
  padding: "10px 12px",
  borderRadius: 10,
  background: isActive ? "rgba(79,140,255,0.18)" : "transparent",
  border: "1px solid rgba(255,255,255,0.08)",
  display: "block",
});

export default function Sidebar() {
  const { role } = authStore.getTokens();

  const canManageUsers = role === "Super Administrateur" || role === "Administrateur";

  return (
    <aside
      style={{
        width: "var(--sidebar-width)",
        padding: 16,
        borderRight: "1px solid var(--border)",
        background: "linear-gradient(180deg, #2a2a2a, #242424)",
      }}
    >
      <div style={{ marginBottom: 16 }}>
        <div style={{ fontWeight: 700, fontSize: 18 }}>EPIC 1</div>
        <div className="small">Authentification</div>
      </div>

      <nav style={{ display: "grid", gap: 10 }}>
        <NavLink to="/admin" style={linkStyle} end>
          Tableau de bord
        </NavLink>

        {canManageUsers && (
          <NavLink to="/admin/users" style={linkStyle}>
            Utilisateurs
          </NavLink>
        )}
      </nav>
    </aside>
  );
}
