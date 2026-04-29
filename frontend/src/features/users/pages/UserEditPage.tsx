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

  if (error) return <div className="text-danger status-text">{error}</div>;
  if (!user) return <div className="small">Chargement...</div>;

  return (
    <div className="form-shell">
      <div className="form-shell-inner">
        <div className="form-header">
          <Link
            to="/admin/users"
            className="btn form-back-link"
          >
            <ArrowLeft size={16} />
            Retour
          </Link>

          <div className="form-header-title">
            <h2 className="page-title">Modifier l’employé</h2>
          </div>

          <div className="form-header-spacer" />
        </div>

        <UserForm
          mode="edit"
          initial={user}
          onSubmit={async (payload) => {
            await usersApi.update(userId, payload as UpdateUserPayload);

            navigate("/admin/users", {
              state: {
                success: "Employé modifié avec succès.",
              },
            });
          }}
        />
      </div>
    </div>
  );
}
