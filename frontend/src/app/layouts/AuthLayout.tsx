import { Outlet, Link } from "react-router-dom";
import logo from "../../shared/assets/logo.svg";

export default function AuthLayout() {
  return (
    <div className="auth-layout">
      <Link to="/login">
        <img
          src={logo}
          alt="Logo"
          className="auth-logo"
        />
      </Link>

      <div className="auth-branding">
        <div className="auth-branding-title">IT-PRO</div>
        <div className="auth-branding-subtitle">
          Informatique & Téléphones
        </div>
      </div>

      <div className="card auth-card">
        <Outlet />
      </div>
    </div>
  );
}
