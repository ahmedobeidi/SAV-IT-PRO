import { Link, useNavigate } from "react-router-dom";
import { ArrowLeft } from "lucide-react";

import RepairOrderCreateForm from "../components/RepairOrderCreateForm";
import { repairsApi } from "../repairs.api";
import type { CreateRepairOrderPayload } from "../repairs.types";

export default function RepairOrderCreatePage() {
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
            to="/admin/repair-orders"
            className="btn"
            style={{ display: "flex", alignItems: "center", gap: 6 }}
          >
            <ArrowLeft size={16} />
            Retour
          </Link>

          <div style={{ textAlign: "center", flex: 1 }}>
            <h2 style={{ margin: 0 }}>Créer un ordre de réparation</h2>
          </div>

          {/* spacer to keep title centered */}
          <div style={{ width: 80 }} />
        </div>

        <RepairOrderCreateForm
          onSubmit={async (payload: CreateRepairOrderPayload) => {
            await repairsApi.create(payload);
            navigate("/admin/repair-orders");
          }}
        />
      </div>
    </div>
  );
}