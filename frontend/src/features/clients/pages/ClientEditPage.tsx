import { Link, useNavigate, useParams } from "react-router-dom";
import { useEffect, useState } from "react";
import { ArrowLeft } from "lucide-react";

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
    <div
      style={{
        minHeight: "70vh",
        display: "flex",
        justifyContent: "center",
        alignItems: "center",
        padding: 12,
      }}
    >
      <div style={{ width: "100%", maxWidth: 720 }}>
        {/* Header with Retour */}
        <div
          style={{
            display: "flex",
            justifyContent: "space-between",
            alignItems: "center",
            marginBottom: 12,
          }}
        >
          <Link
            to="/admin/clients"
            className="btn"
            style={{ display: "flex", alignItems: "center", gap: 6 }}
          >
            <ArrowLeft size={16} />
            Retour
          </Link>

          <div style={{ textAlign: "center", flex: 1 }}>
            <h2 style={{ margin: 0 }}>Modifier le client</h2>
          </div>

          {/* spacer to keep title centered */}
          <div style={{ width: 80 }} />
        </div>

        <ClientForm
          mode="edit"
          initial={client}
          onSubmit={async (payload) => {
            const updated = await clientsApi.update(
              clientId,
              payload as UpdateClientPayload,
            );
            navigate(`/admin/clients/${updated.id}`);
          }}
        />
      </div>
    </div>
  );
}