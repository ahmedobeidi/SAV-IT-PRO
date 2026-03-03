import { useMemo, useState } from "react";
import { useTechnicianRepairOrdersList } from "../hooks/useTechnicianRepairOrdersList";
import type { RepairOrderRead, RepairStatus } from "../repairs.types";
import RepairOrdersTable from "../components/RepairOrdersTable";
import UpdateStatusDialog from "../components/UpdateStatusDialog";
import { repairsApi } from "../repairs.api";
import { mapApiError } from "../repairs.validators";

const STATUS: Array<RepairStatus | ""> = ["", "ASSIGNED","IN_PROGRESS","WAITING_PARTS","DONE","CANCELED"];

export default function TechnicianRepairOrdersPage() {
  const [status, setStatus] = useState<RepairStatus | "">("");
  const [page, setPage] = useState(1);
  const limit = 20;

  const { data, loading, error, refresh } = useTechnicianRepairOrdersList(status, page, limit);
  const totalPages = useMemo(() => {
    if (!data) return 1;
    return Math.max(1, Math.ceil(data.total / data.limit));
  }, [data]);

  const [toast, setToast] = useState<string | null>(null);
  const [statusTarget, setStatusTarget] = useState<RepairOrderRead | null>(null);

  async function techUpdateStatus(newStatus: RepairStatus) {
    if (!statusTarget) return;
    try {
      await repairsApi.technicianUpdateStatus(statusTarget.id, { status: newStatus });
      setToast("Statut mis à jour.");
      refresh();
    } catch (e: any) {
      setToast(mapApiError(e));
    }
  }

  return (
    <div style={{ display: "grid", gap: 12 }}>
      <div>
        <h2 style={{ margin: 0 }}>Mes réparations assignées</h2>
        <div className="small">Espace technicien (EPIC 5)</div>
      </div>

      <div className="card" style={{ padding: 12, display: "flex", gap: 10, flexWrap: "wrap", alignItems: "center" }}>
        <select className="input" value={status} onChange={(e) => { setStatus(e.target.value as any); setPage(1); }} style={{ width: 240 }}>
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
          mode="tech"
          onAssign={() => {}}
          onUpdateStatus={(r) => setStatusTarget(r)}
          onRefresh={refresh}
        />
      )}

      <UpdateStatusDialog
        open={!!statusTarget}
        current={statusTarget?.status ?? "ASSIGNED"}
        // tech: you can restrict (example). If backend already blocks DELIVERED, we just hide it.
        allowed={["ASSIGNED","IN_PROGRESS","WAITING_PARTS","DONE","CANCELED"]}
        onClose={() => setStatusTarget(null)}
        onConfirm={techUpdateStatus}
      />
    </div>
  );
}