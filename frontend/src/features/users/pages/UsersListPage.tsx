import { useEffect, useMemo, useState } from "react";
import { Link, useLocation, useNavigate } from "react-router-dom";
import UsersTable from "../components/UsersTable";
import { useUsersList } from "../hooks/useUsersList";
import { usersApi } from "../users.api";
import type { UserRead } from "../users.types";
import { UserPlus } from "lucide-react";
import ConfirmDialog from "../../../components/ConfirmDialog/ConfirmDialog";

function mapApiError(e: any): string {
  const s = e?.response?.status;
  if (s === 401) return "Session expirée. Reconnecte-toi.";
  if (s === 403) return "Accès interdit (droits insuffisants).";
  if (s === 409) return e?.response?.data?.message ?? "Conflit.";
  if (s === 422) return "Validation échouée.";
  return "Erreur serveur.";
}

type FlashState = {
  success?: string;
  error?: string;
} | null;

export default function UsersListPage() {
  const navigate = useNavigate();
  const location = useLocation();

  const [search, setSearch] = useState("");
  const [page, setPage] = useState(1);
  const limit = 10;

  const { data, loading, error, refresh } = useUsersList(search, page, limit);

  const totalPages = useMemo(() => {
    if (!data) return 1;
    return Math.max(1, Math.ceil(data.total / data.limit));
  }, [data]);

  const [flash, setFlash] = useState<{
    id: number;
    type: "success" | "error";
    text: string;
  } | null>(null);

  function showFlash(type: "success" | "error", text: string) {
    const id = Date.now();
    setFlash({ id, type, text });
    setTimeout(() => {
      setFlash((cur) => (cur?.id === id ? null : cur));
    }, 5000);
  }

  useEffect(() => {
    const state = location.state as FlashState;

    if (state?.success) {
      showFlash("success", state.success);
      navigate(location.pathname, { replace: true });
      return;
    }

    if (state?.error) {
      showFlash("error", state.error);
      navigate(location.pathname, { replace: true });
    }
  }, [location.state, location.pathname, navigate]);

  const [anonymizing, setAnonymizing] = useState<UserRead | null>(null);
  const [toggling, setToggling] = useState<UserRead | null>(null);

  async function toggleActive() {
    if (!toggling) return;

    const user = toggling;
    setToggling(null);

    try {
      await usersApi.setActive(user.id, !user.isActive);
      showFlash(
        "success",
        user.isActive ? "Utilisateur bloqué." : "Utilisateur débloqué."
      );
      refresh();
    } catch (e: any) {
      showFlash("error", mapApiError(e));
      refresh();
    }
  }

  async function anonymize() {
    if (!anonymizing) return;

    const user = anonymizing;
    setAnonymizing(null);

    try {
      await usersApi.anonymize(user.id);
      showFlash("success", "Utilisateur anonymisé (RGPD).");
      refresh();
    } catch (e: any) {
      showFlash("error", mapApiError(e));
      refresh();
    }
  }

  return (
    <div style={{ display: "grid", gap: 12 }}>
      <div
        style={{
          display: "flex",
          justifyContent: "space-between",
          alignItems: "end",
          gap: 12,
        }}
      >
        <div>
          <h2 style={{ margin: 0 }}>Employés</h2>
        </div>

        <Link
          to="/admin/users/new"
          className="btn btn-primary"
          title="Créer un employé"
          aria-label="Créer un employé"
          style={{ display: "flex", alignItems: "center", gap: 6 }}
        >
          <UserPlus size={18} />
          Employé
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
        <div
          style={{
            display: "flex",
            alignItems: "center",
            gap: 10,
            flexWrap: "wrap",
          }}
        >
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

        <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
          <button
            className="btn"
            onClick={() => setPage((p) => Math.max(1, p - 1))}
            disabled={page <= 1}
          >
            Précédent
          </button>

          <button
            className="btn"
            onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
            disabled={page >= totalPages}
          >
            Suivant
          </button>
        </div>
      </div>

      {flash && (
        <div
          className="small"
          style={{
            color:
              flash.type === "success"
                ? "var(--success)"
                : "var(--danger)",
          }}
        >
          {flash.text}
        </div>
      )}

      {loading && <div className="small">Chargement...</div>}
      {error && (
        <div style={{ color: "var(--danger)", fontSize: 13 }}>{error}</div>
      )}

      {data && (
        <UsersTable
          items={data.items}
          onToggleActive={(u) => setToggling(u)}
          onAnonymize={(u) => setAnonymizing(u)}
        />
      )}

      <ConfirmDialog
        open={!!toggling}
        title={toggling?.isActive ? "Bloquer l’employé" : "Débloquer l’employé"}
        message={
          toggling?.isActive
            ? "Confirmer le blocage de cet employé ?"
            : "Confirmer le déblocage de cet employé ?"
        }
        danger={toggling?.isActive}
        confirmText={toggling?.isActive ? "Bloquer" : "Débloquer"}
        cancelText="Annuler"
        onCancel={() => setToggling(null)}
        onConfirm={toggleActive}
      />

      <ConfirmDialog
        open={!!anonymizing}
        title="Anonymiser l’employé"
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