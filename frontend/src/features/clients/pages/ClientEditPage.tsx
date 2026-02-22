import { useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import ClientForm from "../components/ClientForm";
import { clientsApi } from "../clients.api";
import type { ClientRead, UpdateClientPayload } from "../clients.types";

export default function ClientEditPage() {
  const { id } = useParams();
  const clientId = Number(id);
  const navigate = useNavigate();

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
      <div>
        <h2 style={{ margin: 0 }}>Modifier le client</h2>
        <div className="small">EPIC 3 — update client</div>
      </div>

      <ClientForm
        mode="edit"
        initial={client}
        onSubmit={async (payload) => {
          const updated = await clientsApi.update(clientId, payload as UpdateClientPayload);
          navigate(`/admin/clients/${updated.id}`);
        }}
      />
    </div>
  );
}