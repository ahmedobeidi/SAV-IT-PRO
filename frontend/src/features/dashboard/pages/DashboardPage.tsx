import { useDashboardStats } from "../hooks/useDashboardStats";
import { Users, Wrench } from "lucide-react";

export default function DashboardPage() {
  const { data, loading, error } = useDashboardStats();

  return (
    <div className="page-stack">
      <div className="card dashboard-shell">
        <div className="dashboard-header">
          <h2 className="page-title">Tableau de bord</h2>
          {/* <p className="small">
            Cette page est protégée par le <strong>AuthGuard</strong>.
          </p> */}
        </div>

        {loading && <div className="small">Chargement...</div>}
        {error && <div className="text-danger status-text">{error}</div>}

        {data && (
          <div className="dashboard-stats-grid">
            <div className="dashboard-stat-card">
              <div className="dashboard-stat-content">
                <div className="dashboard-stat-label">Clients</div>
                <div className="dashboard-stat-value">{data.clients}</div>
              </div>

              <div className="dashboard-stat-icon dashboard-stat-icon--primary">
                <Users size={22} />
              </div>
            </div>

            <div className="dashboard-stat-card">
              <div className="dashboard-stat-content">
                <div className="dashboard-stat-label">Réparations</div>
                <div className="dashboard-stat-value">{data.repairOrders}</div>
              </div>

              <div className="dashboard-stat-icon dashboard-stat-icon--success">
                <Wrench size={22} />
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}