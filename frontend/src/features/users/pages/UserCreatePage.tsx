import { Link, useNavigate } from "react-router-dom";
import { ArrowLeft } from "lucide-react";

import UserForm from "../components/UserForm";
import { usersApi } from "../users.api";
import type { CreateUserPayload } from "../users.types";

export default function UserCreatePage() {
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
            to="/admin/users"
            className="btn"
            style={{ display: "flex", alignItems: "center", gap: 6 }}
          >
            <ArrowLeft size={16} />
            Retour
          </Link>

          <div style={{ textAlign: "center", flex: 1 }}>
            <h2 style={{ margin: 0 }}>Créer un employé</h2>
          </div>

          {/* spacer to keep title centered */}
          <div style={{ width: 80 }} />
        </div>

        <UserForm
          mode="create"
          onSubmit={async (payload) => {
            await usersApi.create(payload as CreateUserPayload);
            navigate(`/admin/users`);
          }}
        />
      </div>
    </div>
  );
}
