import { useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import UserForm from "../components/UserForm";
import { usersApi } from "../users.api";
import type { UpdateUserPayload, UserRead } from "../users.types";

export default function UserEditPage() {
  const { id } = useParams();
  const userId = Number(id);
  const navigate = useNavigate();

  const [user, setUser] = useState<UserRead | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    usersApi
      .show(userId)
      .then(setUser)
      .catch(() => setError("Accès refusé ou utilisateur introuvable."));
  }, [userId]);

  if (error)
    return <div style={{ color: "var(--danger)", fontSize: 13 }}>{error}</div>;
  if (!user) return <div className="small">Chargement...</div>;

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
            Modifier l’utilisateur
          </h2>
          <div className="small" style={{ textAlign: "center" }}>
          </div>
        </div>

        <UserForm
          mode="edit"
          initial={user}
          onSubmit={async (payload) => {
            const updated = await usersApi.update(
              userId,
              payload as UpdateUserPayload,
            );
            navigate(`/admin/users/${updated.id}`);
          }}
        />
      </div>
    </div>
  );
}
