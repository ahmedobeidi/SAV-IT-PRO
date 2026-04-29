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

  if (error) return <div className="text-danger status-text">{error}</div>;
  if (!client) return <div className="small">Chargement...</div>;

  return (
    <div className="form-shell">
      <div className="form-shell-inner">
        <div className="form-header">
          <Link
            to="/admin/clients"
            className="btn form-back-link"
          >
            <ArrowLeft size={16} />
            Retour
          </Link>

          <div className="form-header-title">
            <h2 className="page-title">Modifier le client</h2>
          </div>

          <div className="form-header-spacer" />
        </div>

        <ClientForm
          mode="edit"
          initial={client}
          onSubmit={async (payload) => {
            await clientsApi.update(clientId, payload as UpdateClientPayload);
            navigate("/admin/clients", {
              state: {
                success: "Client modifié avec succès.",
              },
            });
          }}
        />
      </div>
    </div>
  );
}
