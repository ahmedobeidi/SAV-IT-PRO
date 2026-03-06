import { Outlet, Link } from "react-router-dom";
import logo from "../assets/logo.svg";

export default function AuthLayout() {
  return (
    <div
      style={{
        minHeight: "100%",
        display: "flex",
        flexDirection: 'column',
        justifyContent: "center",
        alignItems: "center",
        padding: 24,
      }}
    >
      <Link to="/login">
        <img
          src={logo}
          alt="Logo"
          style={{ width: 120, marginBottom: 16, borderRadius: 30, cursor: "pointer" }}
        />
      </Link>

      <div style={{ textAlign: "center", marginBottom: 20 }}>
        <div style={{ fontSize: 20, fontWeight: 600 }}>IT-PRO</div>
        <div style={{ fontSize: 13, color: "var(--muted)" }}>
          Informatique & Téléphones
        </div>
      </div>

      <div className="card" style={{ width: "100%", maxWidth: 420, padding: 20 }}>
        <Outlet />
      </div>
    </div>
  );
}
