import { useMemo, useState } from "react";
import { equipmentApi } from "../equipment.api";
import { useEquipmentTypes } from "../hooks/useEquipmentTypes";
import EquipmentNameForm from "../components/EquipmentNameForm";
import EquipmentTypeTable from "../components/EquipmentTypeTable";
import ConfirmDialog from "../../../components/ConfirmDialog";
import type { EquipmentTypeRead } from "../equipment.types";

function mapApiError(e: any): string {
  const s = e?.response?.status;
  if (s === 401) return "Session expirée. Reconnecte-toi.";
  if (s === 403) return "Accès interdit (droits insuffisants).";
  if (s === 409) return e?.response?.data?.message ?? "Conflit: existe déjà ou suppression interdite.";
  if (s === 422) return "Validation échouée.";
  return "Erreur serveur.";
}

export default function EquipmentTypesPage() {
  const [search, setSearch] = useState("");
  const [page, setPage] = useState(1);
  const limit = 20;

  const { data, loading, error, refresh } = useEquipmentTypes(search, page, limit);

  const [toast, setToast] = useState<string | null>(null);

  const totalPages = useMemo(() => {
    if (!data) return 1;
    return Math.max(1, Math.ceil(data.total / data.limit));
  }, [data]);

  // Rename modal state
  const [editing, setEditing] = useState<EquipmentTypeRead | null>(null);

  // Delete confirm
  const [deleting, setDeleting] = useState<EquipmentTypeRead | null>(null);

  async function create(name: string) {
    try {
      await equipmentApi.createType({ name });
      setToast("Type créé.");
      refresh();
    } catch (e: any) {
      setToast(mapApiError(e));
      throw e;
    }
  }

  async function rename(name: string) {
    if (!editing) return;
    try {
      await equipmentApi.updateType(editing.id, { name });
      setToast("Type mis à jour.");
      setEditing(null);
      refresh();
    } catch (e: any) {
      setToast(mapApiError(e));
      throw e;
    }
  }

  async function remove() {
    if (!deleting) return;
    try {
      await equipmentApi.deleteType(deleting.id);
      setToast("Type supprimé.");
      setDeleting(null);
      refresh();
    } catch (e: any) {
      setToast(mapApiError(e));
      setDeleting(null);
    }
  }

  return (
    <div style={{ display: "grid", gap: 12 }}>
      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "end", gap: 12 }}>
        <div>
          <h2 style={{ margin: 0 }}>Équipements — Types</h2>
          <div className="small">Créer, renommer, supprimer (EPIC 4)</div>
        </div>

        <div className="card" style={{ padding: 10, display: "flex", gap: 10, alignItems: "center" }}>
          <input
            className="input"
            placeholder="Rechercher..."
            value={search}
            onChange={(e) => { setSearch(e.target.value); setPage(1); }}
            style={{ width: 260 }}
          />
          <div className="small">Page {page}/{totalPages}</div>
          <button className="btn" onClick={() => setPage((p) => Math.max(1, p - 1))} disabled={page <= 1}>Précédent</button>
          <button className="btn" onClick={() => setPage((p) => Math.min(totalPages, p + 1))} disabled={page >= totalPages}>Suivant</button>
        </div>
      </div>

      {toast && (
        <div className="card" style={{ padding: 12 }}>
          <div className="small">{toast}</div>
          <div style={{ marginTop: 8 }}>
            <button className="btn" onClick={() => setToast(null)}>OK</button>
          </div>
        </div>
      )}

      <EquipmentNameForm submitLabel="Créer un type" onSubmit={create} />

      {loading && <div className="small">Chargement...</div>}
      {error && <div style={{ color: "var(--danger)", fontSize: 13 }}>{error}</div>}

      {data && (
        <EquipmentTypeTable
          items={data.items}
          onEdit={(t) => setEditing(t)}
          onDelete={(t) => setDeleting(t)}
        />
      )}

      {/* Rename quick modal */}
      {editing && (
        <div className="card" style={{ padding: 12 }}>
          <div style={{ fontWeight: 700, marginBottom: 8 }}>Renommer le type</div>
          <EquipmentNameForm
            initialName={editing.name}
            submitLabel="Enregistrer"
            onSubmit={rename}
          />
          <button className="btn" onClick={() => setEditing(null)}>Fermer</button>
        </div>
      )}

      <ConfirmDialog
        open={!!deleting}
        title="Supprimer le type"
        message="Si ce type contient des marques, l’API renverra 409 (suppression interdite)."
        danger
        confirmText="Supprimer"
        onCancel={() => setDeleting(null)}
        onConfirm={remove}
      />
    </div>
  );
}