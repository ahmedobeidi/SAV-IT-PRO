import { Link, useNavigate } from "react-router-dom";
import { ArrowLeft } from "lucide-react";

import ClientForm from "../components/ClientForm";
import { clientsApi } from "../clients.api";
import type { CreateClientPayload } from "../clients.types";

export default function ClientCreatePage() {
  const navigate = useNavigate();

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
            <h2 className="page-title">Créer un client</h2>
          </div>

          <div className="form-header-spacer" />
        </div>

        <ClientForm
          mode="create"
          onSubmit={async (payload) => {
            await clientsApi.create(payload as CreateClientPayload);
            navigate("/admin/clients", {
              state: {
                success: "Client créé avec succès.",
              },
            });
          }}
        />
      </div>
    </div>
  );
}
