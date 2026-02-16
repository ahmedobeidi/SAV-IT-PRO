export default function DashboardPage() {
  return (
    <div className="card" style={{ padding: 16 }}>
      <h2 style={{ marginTop: 0 }}>Tableau de bord</h2>
      <p className="small">
        Cette page est protégée par le <strong>AuthGuard</strong>.
      </p>
    </div>
  );
}
