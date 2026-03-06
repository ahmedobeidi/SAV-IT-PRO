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

      <div className="card" style={{ width: "100%", maxWidth: 420, padding: 20 }}>
        <Outlet />
      </div>
    </div>
  );
}
