import { useMemo, useState } from "react";
import { Link } from "react-router-dom";
import UsersTable from "../components/UsersTable";
import { useUsersList } from "../hooks/useUsersList";
import { usersApi } from "../users.api";
import type { UserRead } from "../users.types";

export default function UsersListPage() {
  const [search, setSearch] = useState("");
  const [page, setPage] = useState(1);
  const limit = 20;

  const { data, loading, error } = useUsersList(search, page, limit);

  const totalPages = useMemo(() => {
    if (!data) return 1;
    return Math.max(1, Math.ceil(data.total / data.limit));
  }, [data]);

  async function onToggleActive(u: UserRead) {
    await usersApi.setActive(u.id, !u.isActive);
    // quick refresh: simplest pro pattern without extra libs
    window.location.reload();
  }

  async function onAnonymize(u: UserRead) {
    const ok = confirm("Confirmer l’anonymisation RGPD ? (action irréversible)");
    if (!ok) return;
    await usersApi.anonymize(u.id);
    window.location.reload();
  }

  return (
    <div style={{ display: "grid", gap: 12 }}>
      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "end", gap: 12 }}>
        <div>
          <h2 style={{ margin: 0 }}>Utilisateurs</h2>
        </div>

        <Link to="/admin/users/new" className="btn btn-primary">
          + Créer un utilisateur
        </Link>
      </div>

      <div className="card" style={{ padding: 12, display: "flex", gap: 10, alignItems: "center" }}>
        <input
          className="input"
          placeholder="Rechercher par nom..."
          value={search}
          onChange={(e) => {
            setSearch(e.target.value);
            setPage(1);
          }}
        />
        <div className="small">Page {page} / {totalPages}</div>
        <button className="btn" onClick={() => setPage((p) => Math.max(1, p - 1))} disabled={page <= 1}>
          Précédent
        </button>
        <button className="btn" onClick={() => setPage((p) => Math.min(totalPages, p + 1))} disabled={page >= totalPages}>
          Suivant
        </button>
      </div>

      {loading && <div className="small">Chargement...</div>}
      {error && <div style={{ color: "var(--danger)", fontSize: 13 }}>{error}</div>}

      {data && (
        <UsersTable items={data.items} onToggleActive={onToggleActive} onAnonymize={onAnonymize} />
      )}
    </div>
  );
}
