import { useNavigate } from "react-router-dom";
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
      }}
    >
      <div style={{ width: "100%", maxWidth: 720 }}>
        <div style={{ marginBottom: 12 }}>
          <h2 style={{ margin: 0, textAlign: "center" }}>
            Créer un utilisateur
          </h2>
          <div className="small" style={{ textAlign: "center" }}>
          </div>
        </div>

        <UserForm
          mode="create"
          onSubmit={async (payload) => {
            const created = await usersApi.create(payload as CreateUserPayload);
            navigate(`/admin/users/${created.id}`);
          }}
        />
      </div>
    </div>
  );
}
