import { useEffect, useState } from "react";
import { Link, useParams } from "react-router-dom";
import { clientsApi } from "../clients.api";
import type { ClientRead } from "../clients.types";

export default function ClientShowPage() {
  const { id } = useParams();
  const clientId = Number(id);

  const [client, setClient] = useState<ClientRead | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    clientsApi
      .show(clientId)
      .then(setClient)
      .catch(() => setError("Accès refusé ou client introuvable."));
  }, [clientId]);

  if (error) return <div style={{ color: "var(--danger)", fontSize: 13 }}>{error}</div>;
  if (!client) return <div className="small">Chargement...</div>;

  return (
    <div style={{ display: "grid", gap: 12 }}>
      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "end" }}>
        <div>
          <h2 style={{ margin: 0 }}>
            {client.lastName} {client.firstName}
          </h2>
          <div className="small">{client.phone} {client.isAnonymized ? "— anonymisé" : ""}</div>
        </div>

        <div style={{ display: "flex", gap: 10, flexWrap: "wrap" }}>
          <Link className="btn" to="/admin/clients">Retour</Link>
          <Link className="btn" to={`/admin/clients/${client.id}/edit`}>Modifier</Link>
          <Link className="btn" to={`/admin/clients/${client.id}/repairs`}>Réparations</Link>
        </div>
      </div>

      <div className="card" style={{ padding: 16, display: "grid", gap: 8, maxWidth: 720 }}>
        <div className="small">Email: <b>{client.email ?? "-"}</b></div>
        <div className="small">Adresse: <b>{client.address ?? "-"}</b></div>
        <div className="small">CP/Ville: <b>{client.postalCode ?? "-"} {client.city ?? ""}</b></div>
        <div className="small">Fixe: <b>{client.landlinePhone ?? "-"}</b></div>
      </div>
    </div>
  );
}