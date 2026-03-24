import { useMemo, useState } from "react";
import { Link } from "react-router-dom";
import { APP_PATHS } from "../../../app/paths";
import { useRepairOrdersList } from "../hooks/useRepairOrdersList";
import type {
  RepairOrderRead,
  RepairStatus,
  UpdateRepairOrderPayload,
} from "../repairs.types";
import RepairOrdersTable from "../components/RepairOrdersTable";
import AssignTechnicianDialog from "../components/AssignTechnicianDialog";
import UpdateStatusDialog from "../components/UpdateStatusDialog";
import { repairsApi } from "../repairs.api";
import BottomPagination from "../../../shared/pagination/BottomPagination";
import { useFlashMessage } from "../../../shared/flash/useFlashMessage";
import { mapCrudApiError } from "../../../shared/errors/mapCrudApiError";
import { getStatusLabel } from "../utils/statusTranslations";
import EditRepairOrderDialog from "../components/EditRepairOrderDialog";
import { useAuth } from "../../auth/useAuth";
import { canAssignTechnician } from "../../auth/auth.roles";

const STATUS: Array<RepairStatus | ""> = [
  "",
  "CREATED",
  "IN_PROGRESS",
  "WAITING_PARTS",
  "DONE",
  "DELIVERED",
  "CANCELED",
];

export default function RepairOrdersListPage() {
  const { role } = useAuth();
  const canAssign = canAssignTechnician(role);

  const [search, setSearch] = useState("");
  const [status, setStatus] = useState<RepairStatus | "">("");
  const [page, setPage] = useState(1);
  const limit = 10;

  const { data, loading, error, refresh } = useRepairOrdersList(
    search,
    status,
    page,
    limit,
  );

  const totalPages = useMemo(() => {
    if (!data) return 1;
    return Math.max(1, Math.ceil(data.total / data.limit));
  }, [data]);
  const { flash, showFlash } = useFlashMessage();

  const [assignTarget, setAssignTarget] = useState<RepairOrderRead | null>(
    null,
  );
  const [statusTarget, setStatusTarget] = useState<RepairOrderRead | null>(
    null,
  );
  const [editTarget, setEditTarget] = useState<RepairOrderRead | null>(null);

  async function assign(technicianId: number | null) {
    if (!assignTarget) return;

    try {
      await repairsApi.assign(assignTarget.id, { technicianId });
      setAssignTarget(null);
      showFlash(
        "success",
        technicianId === null ? "Technicien retiré." : "Technicien affecté.",
      );
      refresh();
    } catch (e: any) {
      showFlash("error", mapCrudApiError(e, { notFoundMessage: "Ordre de réparation introuvable." }));
    }
  }

  async function staffUpdateStatus(newStatus: RepairStatus) {
    if (!statusTarget) return;

    try {
      await repairsApi.staffUpdateStatus(statusTarget.id, {
        status: newStatus,
      });
      setStatusTarget(null);
      showFlash("success", "Statut mis à jour.");
      refresh();
    } catch (e: any) {
      showFlash("error", mapCrudApiError(e, { notFoundMessage: "Ordre de réparation introuvable." }));
    }
  }

  async function updateRepair(payload: UpdateRepairOrderPayload) {
    if (!editTarget) return;

    const repair = editTarget;
    setEditTarget(null);

    try {
      await repairsApi.update(repair.id, payload);
      showFlash("success", "Réparation mise à jour.");
      refresh();
    } catch (e: any) {
      showFlash("error", mapCrudApiError(e, { notFoundMessage: "Ordre de réparation introuvable." }));
      refresh();
      throw e;
    }
  }

  return (
    <div className="page-stack">
      <div className="page-header">
        <div>
          <h2 className="page-title">Réparations</h2>
        </div>

        <Link
          to={APP_PATHS.repairOrdersNew}
          className="btn btn-primary"
        >
          Créer
        </Link>
      </div>

      <div
        className="card"
        style={{
          padding: 12,
          display: "flex",
          gap: 10,
          flexWrap: "wrap",
          alignItems: "center",
        }}
      >
        <input
          className="input"
          placeholder="Rechercher par nom, téléphone ou référence SAV-2026-000123"
          value={search}
          onChange={(e) => {
            setSearch(e.target.value);
            setPage(1);
          }}
          className="input page-search-input-wide"
        />

        <select
          className="input"
          value={status}
          onChange={(e) => {
            setStatus(e.target.value as RepairStatus | "");
            setPage(1);
          }}
          className="input page-select-input"
        >
          {STATUS.map((s) => (
            <option key={s || "ALL"} value={s}>
              {s ? getStatusLabel(s) : "Tous statuts"}
            </option>
          ))}
        </select>

        <div className="small push-right">
          Page {page}/{totalPages}
        </div>

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
          <RepairOrdersTable
            items={data.items}
            onEdit={(r) => setEditTarget(r)}
            onAssign={canAssign ? (r) => setAssignTarget(r) : undefined}
            onUpdateStatus={(r) => setStatusTarget(r)}
            onRefresh={refresh}
            onMessage={showFlash}
          />

          <BottomPagination
            page={page}
            totalPages={totalPages}
            onChange={setPage}
          />
        </>
      )}

      {canAssign && (
        <AssignTechnicianDialog
          open={!!assignTarget}
          onClose={() => setAssignTarget(null)}
          onConfirm={assign}
        />
      )}

      <UpdateStatusDialog
        open={!!statusTarget}
        current={statusTarget?.status ?? "CREATED"}
        allowed={[
          "CREATED",
          "IN_PROGRESS",
          "WAITING_PARTS",
          "DONE",
          "DELIVERED",
          "CANCELED",
        ]}
        onClose={() => setStatusTarget(null)}
        onConfirm={staffUpdateStatus}
      />

      <EditRepairOrderDialog
        open={!!editTarget}
        repair={editTarget}
        onClose={() => setEditTarget(null)}
        onSubmit={updateRepair}
      />
    </div>
  );
}
