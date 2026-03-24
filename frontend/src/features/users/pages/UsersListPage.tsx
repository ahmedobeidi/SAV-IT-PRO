import { useEffect, useMemo, useState } from "react";
import { Link, useLocation, useNavigate } from "react-router-dom";
import { APP_PATHS } from "../../../app/paths";
import UsersTable from "../components/UsersTable";
import { useUsersList } from "../hooks/useUsersList";
import { usersApi } from "../users.api";
import type { UserRead } from "../users.types";
import { UserPlus } from "lucide-react";
import ConfirmDialog from "../../../shared/components/ConfirmDialog/ConfirmDialog";
import BottomPagination from "../../../shared/pagination/BottomPagination";
import { useFlashMessage } from "../../../shared/flash/useFlashMessage";
import { mapCrudApiError } from "../../../shared/errors/mapCrudApiError";

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
  const { flash, showFlash } = useFlashMessage();

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
      showFlash("error", mapCrudApiError(e));
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
      showFlash("error", mapCrudApiError(e));
      refresh();
    }
  }

  return (
    <div className="page-stack">
      <div className="page-header">
        <div>
          <h2 className="page-title">Employés</h2>
        </div>

        <Link
          to={APP_PATHS.usersNew}
          className="btn btn-primary inline-actions"
          title="Créer un employé"
          aria-label="Créer un employé"
        >
          <UserPlus size={18} />
          Employé
        </Link>
      </div>

      <div className="card page-toolbar">
        <div className="page-toolbar-group">
          <input
            className="input page-search-input"
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

        <div className="page-toolbar-actions">
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
          className={`small ${flash.type === "success" ? "flash-success" : "flash-error"}`}
        >
          {flash.text}
        </div>
      )}

      {loading && <div className="small">Chargement...</div>}
      {error && (
        <div className="text-danger status-text">{error}</div>
      )}

      {data && (
        <>
          <UsersTable
            items={data.items}
            onToggleActive={(u) => setToggling(u)}
            onAnonymize={(u) => setAnonymizing(u)}
          />

          <BottomPagination
            page={page}
            totalPages={totalPages}
            onChange={setPage}
          />
        </>
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
