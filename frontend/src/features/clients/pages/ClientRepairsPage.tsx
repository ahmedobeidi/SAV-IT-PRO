import { useEffect, useState } from "react";
import { Link, useParams } from "react-router-dom";
import { clientsApi } from "../clients.api";

export default function ClientRepairsPage() {
  const { id } = useParams();
  const clientId = Number(id);

  const [items, setItems] = useState<any[] | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    clientsApi
      .repairs(clientId)
      .then((res) => setItems(res.items))
      .catch(() => setError("Impossible de charger l’historique des réparations."));
  }, [clientId]);

  if (error) return <div style={{ color: "var(--danger)", fontSize: 13 }}>{error}</div>;
  if (!items) return <div className="small">Chargement...</div>;

  return (
    <div style={{ display: "grid", gap: 12 }}>
      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "end" }}>
        <div>
          <h2 style={{ margin: 0 }}>Réparations du client</h2>
          <div className="small">Historique (EPIC 3 #20)</div>
        </div>
        <Link className="btn" to={`/admin/clients/${clientId}`}>Retour client</Link>
      </div>

      <div className="card" style={{ padding: 16 }}>
        <pre style={{ whiteSpace: "pre-wrap", margin: 0 }}>
          {items.length ? JSON.stringify(items, null, 2) : "Aucune réparation."}
        </pre>
      </div>
    </div>
  );
}