import { useNavigate } from "react-router-dom";
import ClientForm from "../components/ClientForm";
import { clientsApi } from "../clients.api";
import type { CreateClientPayload } from "../clients.types";

export default function ClientCreatePage() {
  const navigate = useNavigate();

  return (
    <div style={{ display: "grid", gap: 12 }}>
      <div>
        <h2 style={{ margin: 0 }}>Créer un client</h2>
        <div className="small">EPIC 3 — création client</div>
      </div>

      <ClientForm
        mode="create"
        onSubmit={async (payload) => {
          const created = await clientsApi.create(payload as CreateClientPayload);
          navigate(`/admin/clients/${created.id}`);
        }}
      />
    </div>
  );
}