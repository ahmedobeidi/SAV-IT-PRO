import { useEffect, useMemo, useState } from "react";
import { Link, useLocation, useNavigate } from "react-router-dom";
import { APP_PATHS } from "../../../app/paths";
import ClientsTable from "../components/ClientsTable";
import { useClientsList } from "../hooks/useClientsList";
import { clientsApi } from "../clients.api";
import type { ClientRead } from "../clients.types";
import { UserPlus } from "lucide-react";
import ConfirmDialog from "../../../shared/components/ConfirmDialog/ConfirmDialog";
import BottomPagination from "../../../shared/pagination/BottomPagination";
import { useFlashMessage } from "../../../shared/flash/useFlashMessage";
import { mapCrudApiError } from "../../../shared/errors/mapCrudApiError";

type FlashState = {
  success?: string;
  error?: string;
} | null;

export default function ClientsListPage() {
  const navigate = useNavigate();
  const location = useLocation();

  const [phone, setPhone] = useState("");
  const [page, setPage] = useState(1);
  const limit = 10;

  const { data, loading, error, refresh } = useClientsList(phone, page, limit);

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

  const [anonymizing, setAnonymizing] = useState<ClientRead | null>(null);

  async function anonymize() {
    if (!anonymizing) return;

    const client = anonymizing;
    setAnonymizing(null);

    try {
      await clientsApi.anonymize(client.id);
      showFlash("success", "Client anonymisé (RGPD).");
      refresh();
    } catch (e: any) {
      showFlash("error", mapCrudApiError(e, { notFoundMessage: "Client introuvable." }));
      refresh();
    }
  }

  return (
    <div className="page-stack">
      <div className="page-header">
        <div>
          <h2 className="page-title">Clients</h2>
        </div>

        <Link
          to={APP_PATHS.clientsNew}
          className="btn btn-primary inline-actions"
          title="Créer un client"
          aria-label="Créer un client"
        >
          <UserPlus size={18} />
          Client
        </Link>
      </div>

      <div className="card page-toolbar">
        <div className="page-toolbar-group">
          <input
            className="input page-search-input"
            placeholder="Rechercher par téléphone..."
            value={phone}
            onChange={(e) => {
              setPhone(e.target.value);
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
          <ClientsTable
            items={data.items}
            onAnonymize={(c) => setAnonymizing(c)}
          />

          <BottomPagination
            page={page}
            totalPages={totalPages}
            onChange={setPage}
          />
        </>
      )}

      <ConfirmDialog
        open={!!anonymizing}
        title="Anonymiser le client"
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
