import { useMemo, useState } from "react";
import { Link, useParams } from "react-router-dom";
import { equipmentApi } from "../equipment.api";
import { useEquipmentModels } from "../hooks/useEquipmentModels";
import EquipmentNameForm from "../components/EquipmentNameForm";
import EquipmentModelTable from "../components/EquipmentModelTable";
import ConfirmDialog from "../components/ConfirmDialog";
import type { EquipmentModelRead } from "../equipment.types";

function mapApiError(e: any): string {
  const s = e?.response?.status;
  if (s === 401) return "Session expirée. Reconnecte-toi.";
  if (s === 403) return "Accès interdit.";
  if (s === 409) return e?.response?.data?.message ?? "Conflit: existe déjà / suppression interdite.";
  if (s === 422) return "Validation échouée.";
  return "Erreur serveur.";
}

export default function EquipmentModelsPage() {
  const { brandId } = useParams();
  const bid = Number(brandId);

  const [search, setSearch] = useState("");
  const [page, setPage] = useState(1);
  const limit = 20;

  const { data, loading, error, refresh } = useEquipmentModels(bid, search, page, limit);

  const [toast, setToast] = useState<string | null>(null);
  const totalPages = useMemo(() => {
    if (!data) return 1;
    return Math.max(1, Math.ceil(data.total / data.limit));
  }, [data]);

  const [editing, setEditing] = useState<EquipmentModelRead | null>(null);
  const [deleting, setDeleting] = useState<EquipmentModelRead | null>(null);

  async function create(name: string) {
    try {
      await equipmentApi.createModel(bid, { name });
      setToast("Modèle créé.");
      refresh();
    } catch (e: any) {
      setToast(mapApiError(e));
      throw e;
    }
  }

  async function rename(name: string) {
    if (!editing) return;
    try {
      await equipmentApi.updateModel(editing.id, { name });
      setToast("Modèle mis à jour.");
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
      await equipmentApi.deleteModel(deleting.id);
      setToast("Modèle supprimé.");
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
          <h2 style={{ margin: 0 }}>Équipements — Modèles</h2>
          <div className="small">Brand ID: {bid}</div>
        </div>

        <div style={{ display: "flex", gap: 10 }}>
          <Link className="btn" to="/admin/equipment/types">← Retour Types</Link>

          <div className="card" style={{ padding: 10, display: "flex", gap: 10, alignItems: "center" }}>
            <input
              className="input"
              placeholder="Rechercher..."
              value={search}
              onChange={(e) => { setSearch(e.target.value); setPage(1); }}
              style={{ width: 240 }}
            />
            <div className="small">Page {page}/{totalPages}</div>
            <button className="btn" onClick={() => setPage((p) => Math.max(1, p - 1))} disabled={page <= 1}>Précédent</button>
            <button className="btn" onClick={() => setPage((p) => Math.min(totalPages, p + 1))} disabled={page >= totalPages}>Suivant</button>
          </div>
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

      <EquipmentNameForm submitLabel="Créer un modèle" onSubmit={create} />

      {loading && <div className="small">Chargement...</div>}
      {error && <div style={{ color: "var(--danger)", fontSize: 13 }}>{error}</div>}

      {data && (
        <EquipmentModelTable
          items={data.items}
          onEdit={(m) => setEditing(m)}
          onDelete={(m) => setDeleting(m)}
        />
      )}

      {editing && (
        <div className="card" style={{ padding: 12 }}>
          <div style={{ fontWeight: 700, marginBottom: 8 }}>Renommer le modèle</div>
          <EquipmentNameForm initialName={editing.name} submitLabel="Enregistrer" onSubmit={rename} />
          <button className="btn" onClick={() => setEditing(null)}>Fermer</button>
        </div>
      )}

      <ConfirmDialog
        open={!!deleting}
        title="Supprimer le modèle"
        message="Si ce modèle est utilisé dans des réparations, l’API renverra 409."
        danger
        confirmText="Supprimer"
        onCancel={() => setDeleting(null)}
        onConfirm={remove}
      />
    </div>
  );
}