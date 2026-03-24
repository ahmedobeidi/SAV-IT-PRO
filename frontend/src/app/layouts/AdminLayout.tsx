import { Outlet } from "react-router-dom";
import Sidebar from "../../shared/components/Sidebar/Sidebar";
import Topbar from "../../shared/components/Topbar/Topbar";

export default function AdminLayout() {
  return (
    <div className="app-shell">
      <Sidebar />

      <div className="app-main">
        <Topbar />
        <main className="app-content">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
