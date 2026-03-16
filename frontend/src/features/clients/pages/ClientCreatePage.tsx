import { Link, useNavigate } from "react-router-dom";
import { ArrowLeft } from "lucide-react";

import ClientForm from "../components/ClientForm";
import { clientsApi } from "../clients.api";
import type { CreateClientPayload } from "../clients.types";

export default function ClientCreatePage() {
  const navigate = useNavigate();

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
            <h2 style={{ margin: 0 }}>Créer un client</h2>
          </div>

          <div style={{ width: 80 }} />
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