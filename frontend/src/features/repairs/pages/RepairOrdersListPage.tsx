import { useMemo, useState } from "react";
import { Link } from "react-router-dom";
import { useRepairOrdersList } from "../hooks/useRepairOrdersList";
import type { RepairOrderRead, RepairStatus } from "../repairs.types";
import RepairOrdersTable from "../components/RepairOrdersTable";
import AssignTechnicianDialog from "../components/AssignTechnicianDialog";
import UpdateStatusDialog from "../components/UpdateStatusDialog";
import { repairsApi } from "../repairs.api";
import { mapApiError } from "../repairs.validators";

const STATUS: Array<RepairStatus | ""> = ["", "CREATED", "ASSIGNED", "IN_PROGRESS", "WAITING_PARTS", "DONE", "DELIVERED", "CANCELED"];

export default function RepairOrdersListPage() {
  const [search, setSearch] = useState("");
  const [status, setStatus] = useState<RepairStatus | "">("");
  const [page, setPage] = useState(1);
  const limit = 20;

  const { data, loading, error, refresh } = useRepairOrdersList(search, status, page, limit);

  const totalPages = useMemo(() => {
    if (!data) return 1;
    return Math.max(1, Math.ceil(data.total / data.limit));
  }, [data]);

  const [toast, setToast] = useState<string | null>(null);
  const [assignTarget, setAssignTarget] = useState<RepairOrderRead | null>(null);
  const [statusTarget, setStatusTarget] = useState<RepairOrderRead | null>(null);

  async function assign(technicianId: number) {
    if (!assignTarget) return;
    try {
      await repairsApi.assign(assignTarget.id, { technicianId });
      setToast("Technicien affecté.");
      refresh();
    } catch (e: any) {
      setToast(mapApiError(e));
    }
  }

  async function staffUpdateStatus(newStatus: RepairStatus) {
    if (!statusTarget) return;
    try {
      await repairsApi.staffUpdateStatus(statusTarget.id, { status: newStatus });
      setToast("Statut mis à jour.");
      refresh();
    } catch (e: any) {
      setToast(mapApiError(e));
    }
  }

  return (
    <div style={{ display: "grid", gap: 12 }}>
      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "end", gap: 12 }}>
        <div>
          <h2 style={{ margin: 0 }}>Réparations</h2>
        </div>

        <Link
          to="/admin/repair-orders/new"
          className="btn btn-primary"
          style={{
            display: "inline-block",
            lineHeight: "1",
            textDecoration: "none"
          }}
        >
          Créer
        </Link>
      </div>

      <div className="card" style={{ padding: 12, display: "flex", gap: 10, flexWrap: "wrap", alignItems: "center" }}>
        <input
          className="input"
          placeholder="Search client (nom / téléphone)"
          value={search}
          onChange={(e) => { setSearch(e.target.value); setPage(1); }}
          style={{ width: 300 }}
        />

        <select className="input" value={status} onChange={(e) => { setStatus(e.target.value as any); setPage(1); }} style={{ width: 220 }}>
          {STATUS.map((s) => (
            <option key={s || "ALL"} value={s}>{s ? s : "Tous statuts"}</option>
          ))}
        </select>

        <div className="small" style={{ marginLeft: "auto" }}>
          Page {page}/{totalPages}
        </div>
        <button className="btn" onClick={() => setPage((p) => Math.max(1, p - 1))} disabled={page <= 1}>Précédent</button>
        <button className="btn" onClick={() => setPage((p) => Math.min(totalPages, p + 1))} disabled={page >= totalPages}>Suivant</button>
      </div>

      {toast && (
        <div className="card" style={{ padding: 12 }}>
          <div className="small">{toast}</div>
          <div style={{ marginTop: 8 }}>
            <button className="btn" onClick={() => setToast(null)}>OK</button>
          </div>
        </div>
      )}

      {loading && <div className="small">Chargement...</div>}
      {error && <div style={{ color: "var(--danger)", fontSize: 13 }}>{error}</div>}

      {data && (
        <RepairOrdersTable
          items={data.items}
          mode="staff"
          onAssign={(r) => setAssignTarget(r)}
          onUpdateStatus={(r) => setStatusTarget(r)}
          onRefresh={refresh}
        />
      )}

      <AssignTechnicianDialog
        open={!!assignTarget}
        onClose={() => setAssignTarget(null)}
        onConfirm={assign}
      />

      <UpdateStatusDialog
        open={!!statusTarget}
        current={statusTarget?.status ?? "CREATED"}
        allowed={["CREATED", "ASSIGNED", "IN_PROGRESS", "WAITING_PARTS", "DONE", "DELIVERED", "CANCELED"]}
        onClose={() => setStatusTarget(null)}
        onConfirm={staffUpdateStatus}
      />
    </div>
  );
}