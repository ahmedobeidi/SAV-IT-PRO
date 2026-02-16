import { Link, useNavigate, useParams } from "react-router-dom";
import { useEffect, useState } from "react";
import { ArrowLeft } from "lucide-react";

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

  if (error) return <div style={{ color: "var(--danger)", fontSize: 13 }}>{error}</div>;
  if (!user) return <div className="small">Chargement...</div>;

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
            <h2 style={{ margin: 0 }}>Modifier l’utilisateur</h2>
          </div>

          {/* spacer to keep title centered */}
          <div style={{ width: 80 }} />
        </div>

        <UserForm
          mode="edit"
          initial={user}
          onSubmit={async (payload) => {
            const updated = await usersApi.update(userId, payload as UpdateUserPayload);
            navigate(`/admin/users/${updated.id}`);
          }}
        />
      </div>
    </div>
  );
}
