import { Outlet } from "react-router-dom";

export default function AuthLayout() {
  return (
    <div
      style={{
        minHeight: "100%",
        display: "grid",
        placeItems: "center",
        padding: 24,
      }}
    >
      <div className="card" style={{ width: "100%", maxWidth: 420, padding: 20 }}>
        <Outlet />
      </div>
    </div>
  );
}
