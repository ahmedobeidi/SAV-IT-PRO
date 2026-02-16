import { useEffect, useState } from "react";
import { Link, useParams } from "react-router-dom";
import { usersApi } from "../users.api";
import type { UserRead } from "../users.types";
import RoleBadge from "../components/RoleBadge";

export default function UserShowPage() {
  const { id } = useParams();
  const userId = Number(id);

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
    <div style={{ display: "grid", gap: 12 }}>
      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "end" }}>
        <div>
          <h2 style={{ margin: 0 }}>
            {user.lastName} {user.firstName}
          </h2>
          <div className="small">{user.email}</div>
        </div>

        <div style={{ display: "flex", gap: 10 }}>
          <Link className="btn" to="/admin/users">Retour</Link>
          <Link className="btn" to={`/admin/users/${user.id}/edit`}>Modifier</Link>
        </div>
      </div>

      <div className="card" style={{ padding: 16, display: "grid", gap: 10, maxWidth: 720 }}>
        <div><RoleBadge role={user.role} /></div>
        <div className="small">Statut: <b>{user.isActive ? "Actif" : "Bloqué"}</b></div>
        <div className="small">Anonymisé: <b>{user.isAnonymized ? "Oui" : "Non"}</b></div>
      </div>
    </div>
  );
}
