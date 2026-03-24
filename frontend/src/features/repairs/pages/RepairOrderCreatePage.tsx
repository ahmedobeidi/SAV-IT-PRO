import { Link, useNavigate } from "react-router-dom";
import { ArrowLeft } from "lucide-react";

import RepairOrderCreateForm from "../components/RepairOrderCreateForm";
import { repairsApi } from "../repairs.api";
import type { CreateRepairOrderPayload } from "../repairs.types";

export default function RepairOrderCreatePage() {
  const navigate = useNavigate();

  return (
    <div className="form-shell">
      <div className="form-shell-inner">
        {/* Header with Retour */}
        <div className="form-header">
          <Link
            to="/admin/repair-orders"
            className="btn form-back-link"
          >
            <ArrowLeft size={16} />
            Retour
          </Link>

          <div className="form-header-title">
            <h2 className="page-title">Créer un ordre de réparation</h2>
          </div>

          {/* spacer to keep title centered */}
          <div className="form-header-spacer" />
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
