import { useMemo, useState } from "react";
import { Link } from "react-router-dom";
import UsersTable from "../components/UsersTable";
import { useUsersList } from "../hooks/useUsersList";
import { usersApi } from "../users.api";
import type { UserRead } from "../users.types";
import { UserPlus } from "lucide-react";
import ConfirmDialog from "../../../components/ConfirmDialog"; // ✅ adjust path if needed

export default function UsersListPage() {
  const [search, setSearch] = useState("");
  const [page, setPage] = useState(1);
  const limit = 10;

  const { data, loading, error } = useUsersList(search, page, limit);

  const totalPages = useMemo(() => {
    if (!data) return 1;
    return Math.max(1, Math.ceil(data.total / data.limit));
  }, [data]);

  // ✅ anonymize confirm dialog state
  const [anonymizing, setAnonymizing] = useState<UserRead | null>(null);

  async function onToggleActive(u: UserRead) {
    await usersApi.setActive(u.id, !u.isActive);
    window.location.reload();
  }

  async function anonymize() {
    if (!anonymizing) return;
    await usersApi.anonymize(anonymizing.id);
    setAnonymizing(null);
    window.location.reload();
  }

  return (
    <div style={{ display: "grid", gap: 12 }}>
      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "end", gap: 12 }}>
        <div>
          <h2 style={{ margin: 0 }}>Utilisateurs</h2>
        </div>

        <Link
          to="/admin/users/new"
          className="btn btn-primary"
          title="Créer un utilisateur"
          aria-label="Créer un utilisateur"
        >
          <UserPlus size={18} />
        </Link>
      </div>

      <div
        className="card"
        style={{
          padding: 12,
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
          flexWrap: "wrap",
          gap: 10,
        }}
      >
        {/* LEFT */}
        <div style={{ display: "flex", alignItems: "center", gap: 10, flexWrap: "wrap" }}>
          <input
            className="input"
            style={{ width: 260, flexShrink: 0 }}
            placeholder="Rechercher par nom..."
            value={search}
            onChange={(e) => {
              setSearch(e.target.value);
              setPage(1);
            }}
          />

          <div className="small">
            Page {page} / {totalPages}
          </div>
        </div>

        {/* RIGHT */}
        <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
          <button className="btn" onClick={() => setPage((p) => Math.max(1, p - 1))} disabled={page <= 1}>
            Précédent
          </button>

          <button className="btn" onClick={() => setPage((p) => Math.min(totalPages, p + 1))} disabled={page >= totalPages}>
            Suivant
          </button>
        </div>
      </div>

      {loading && <div className="small">Chargement...</div>}
      {error && <div style={{ color: "var(--danger)", fontSize: 13 }}>{error}</div>}

      {data && (
        <UsersTable
          items={data.items}
          onToggleActive={onToggleActive}
          onAnonymize={(u) => setAnonymizing(u)} // ✅ instead of confirm()
        />
      )}

      <ConfirmDialog
        open={!!anonymizing}
        title="Anonymiser l’utilisateur"
        message="Confirmer l’anonymisation RGPD ? (action irréversible)"
        danger
        confirmText="Anonymiser"
        cancelText="Annuler"
        onCancel={() => setAnonymizing(null)}
        onConfirm={anonymize}
      />
    </div>
  );
}