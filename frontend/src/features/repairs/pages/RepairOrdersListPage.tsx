import { useMemo, useState } from "react";
import { Link } from "react-router-dom";
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
import { mapApiError } from "../repairs.validators";
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

type BottomPaginationProps = {
  page: number;
  totalPages: number;
  onChange: (page: number) => void;
};

function buildPageItems(page: number, totalPages: number): (number | "...")[] {
  if (totalPages <= 7) {
    return Array.from({ length: totalPages }, (_, i) => i + 1);
  }

  const items: (number | "...")[] = [1];

  const start = Math.max(2, page - 1);
  const end = Math.min(totalPages - 1, page + 1);

  if (start > 2) items.push("...");
  for (let p = start; p <= end; p++) items.push(p);
  if (end < totalPages - 1) items.push("...");
  items.push(totalPages);

  return items;
}

function BottomPagination({
  page,
  totalPages,
  onChange,
}: BottomPaginationProps) {
  if (totalPages <= 1) return null;

  const items = buildPageItems(page, totalPages);

  return (
    <div
      style={{
        display: "flex",
        justifyContent: "center",
        alignItems: "center",
        gap: 8,
        flexWrap: "wrap",
        paddingTop: 6,
      }}
    >
      {items.map((item, i) =>
        item === "..." ? (
          <span key={`dots-${i}`} className="small">
            …
          </span>
        ) : (
          <button
            key={item}
            className="btn"
            onClick={() => onChange(item)}
            aria-current={item === page ? "page" : undefined}
            style={{
              minWidth: 38,
              fontWeight: item === page ? 700 : 400,
              border:
                item === page
                  ? "1px solid var(--primary)"
                  : "1px solid var(--border)",
              background: item === page ? "var(--primary)" : "transparent",
              color: item === page ? "#fff" : "inherit",
              cursor: "pointer",
            }}
          >
            {item}
          </button>
        ),
      )}
    </div>
  );
}

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
      showFlash("error", mapApiError(e));
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
      showFlash("error", mapApiError(e));
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
      showFlash("error", mapApiError(e));
      refresh();
      throw e;
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
          <h2 style={{ margin: 0 }}>Réparations</h2>
        </div>

        <Link
          to="/admin/repair-orders/new"
          className="btn btn-primary"
          style={{
            display: "inline-block",
            lineHeight: "1",
            textDecoration: "none",
          }}
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
          style={{ width: 300 }}
        />

        <select
          className="input"
          value={status}
          onChange={(e) => {
            setStatus(e.target.value as RepairStatus | "");
            setPage(1);
          }}
          style={{ width: 220 }}
        >
          {STATUS.map((s) => (
            <option key={s || "ALL"} value={s}>
              {s ? getStatusLabel(s) : "Tous statuts"}
            </option>
          ))}
        </select>

        <div className="small" style={{ marginLeft: "auto" }}>
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
          className="small"
          style={{
            color:
              flash.type === "success" ? "var(--success)" : "var(--danger)",
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