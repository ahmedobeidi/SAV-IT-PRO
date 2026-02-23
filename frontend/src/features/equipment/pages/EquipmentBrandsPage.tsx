import { useMemo, useState } from "react";
import { Link, useParams } from "react-router-dom";
import { equipmentApi } from "../equipment.api";
import { useEquipmentBrands } from "../hooks/useEquipmentBrands";
import EquipmentNameForm from "../components/EquipmentNameForm";
import EquipmentBrandTable from "../components/EquipmentBrandTable";
import ConfirmDialog from "../../../components/ConfirmDialog";
import type { EquipmentBrandRead } from "../equipment.types";

function mapApiError(e: any): string {
  const s = e?.response?.status;
  if (s === 401) return "Session expirée. Reconnecte-toi.";
  if (s === 403) return "Accès interdit.";
  if (s === 409) return e?.response?.data?.message ?? "Conflit: existe déjà / suppression interdite.";
  if (s === 422) return "Validation échouée.";
  return "Erreur serveur.";
}

export default function EquipmentBrandsPage() {
  const { typeId } = useParams();
  const tid = Number(typeId);

  const [search, setSearch] = useState("");
  const [page, setPage] = useState(1);
  const limit = 20;

  const { data, loading, error, refresh } = useEquipmentBrands(tid, search, page, limit);

  const [toast, setToast] = useState<string | null>(null);
  const totalPages = useMemo(() => {
    if (!data) return 1;
    return Math.max(1, Math.ceil(data.total / data.limit));
  }, [data]);

  const [editing, setEditing] = useState<EquipmentBrandRead | null>(null);
  const [deleting, setDeleting] = useState<EquipmentBrandRead | null>(null);

  async function create(name: string) {
    try {
      await equipmentApi.createBrand(tid, { name });
      setToast("Marque créée.");
      refresh();
    } catch (e: any) {
      setToast(mapApiError(e));
      throw e;
    }
  }

  async function rename(name: string) {
    if (!editing) return;
    try {
      await equipmentApi.updateBrand(editing.id, { name });
      setToast("Marque mise à jour.");
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
      await equipmentApi.deleteBrand(deleting.id);
      setToast("Marque supprimée.");
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
          <h2 style={{ margin: 0 }}>Équipements — Marques</h2>
          <div className="small">Type ID: {tid}</div>
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

      <EquipmentNameForm submitLabel="Créer une marque" onSubmit={create} />

      {loading && <div className="small">Chargement...</div>}
      {error && <div style={{ color: "var(--danger)", fontSize: 13 }}>{error}</div>}

      {data && (
        <EquipmentBrandTable
          items={data.items}
          onEdit={(b) => setEditing(b)}
          onDelete={(b) => setDeleting(b)}
        />
      )}

      {editing && (
        <div className="card" style={{ padding: 12 }}>
          <div style={{ fontWeight: 700, marginBottom: 8 }}>Renommer la marque</div>
          <EquipmentNameForm initialName={editing.name} submitLabel="Enregistrer" onSubmit={rename} />
          <button className="btn" onClick={() => setEditing(null)}>Fermer</button>
        </div>
      )}

      <ConfirmDialog
        open={!!deleting}
        title="Supprimer la marque"
        message="Si cette marque contient des modèles, l’API renverra 409."
        danger
        confirmText="Supprimer"
        onCancel={() => setDeleting(null)}
        onConfirm={remove}
      />
    </div>
  );
}