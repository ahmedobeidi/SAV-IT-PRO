import { useNavigate } from "react-router-dom";
import RepairOrderCreateForm from "../components/RepairOrderCreateForm";
import { repairsApi } from "../repairs.api";
import type { CreateRepairOrderPayload } from "../repairs.types";

export default function RepairOrderCreatePage() {
  const navigate = useNavigate();

  return (
    <div style={{ display: "grid", gap: 12 }}>
      <div>
        <h2 style={{ margin: 0 }}>Créer un ordre de réparation</h2>
        <div className="small">Client (recherche téléphone) + Équipement (Type → Marque → Modèle)</div>
      </div>

      <RepairOrderCreateForm
        onSubmit={async (payload: CreateRepairOrderPayload) => {
          await repairsApi.create(payload);
          navigate("/admin/repair-orders");
        }}
      />
    </div>
  );
}