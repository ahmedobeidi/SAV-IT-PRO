import { Outlet } from "react-router-dom";
import Sidebar from "../components/Sidebar.tsx";
import Topbar from "../components/Topbar.tsx";

export default function AdminLayout() {
  return (
    <div style={{ display: "flex", minHeight: "100%" }}>
      <Sidebar />

      <div style={{ flex: 1, display: "flex", flexDirection: "column" }}>
        <Topbar />
        <main style={{ padding: 20 }}>
          <Outlet />
        </main>
      </div>
    </div>
  );
}
