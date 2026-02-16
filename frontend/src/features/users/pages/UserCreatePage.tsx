import { useNavigate } from "react-router-dom";
import UserForm from "../components/UserForm";
import { usersApi } from "../users.api";
import type { CreateUserPayload } from "../users.types";

export default function UserCreatePage() {
  const navigate = useNavigate();

  return (
    <div style={{ display: "grid", gap: 12 }}>
      <div>
        <h2 style={{ margin: 0 }}>Créer un utilisateur</h2>
      </div>

      <UserForm
        mode="create"
        onSubmit={async (payload) => {
          const created = await usersApi.create(payload as CreateUserPayload);
          navigate(`/admin/users/${created.id}`);
        }}
      />
    </div>
  );
}
