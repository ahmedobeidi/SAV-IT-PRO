// ✅ EquipmentBrandsPage.tsx (FULL FINAL)
// Same UX as Types page:
// - Create opens modal (blur background)
// - Modal closes immediately on submit (success OR error)
// - Message under search (green/red) for 7 seconds
// - No toast, no OK button, no border styling
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
  if (s === 409)
    return e?.response?.data?.message ?? "Conflit: existe déjà / suppression interdite.";
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

  const totalPages = useMemo(() => {
    if (!data) return 1;
    return Math.max(1, Math.ceil(data.total / data.limit));
  }, [data]);

  // ✅ flash message (7s auto-hide)
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
    }, 7000);
  }

  // ✅ modal create
  const [creating, setCreating] = useState(false);

  const [editing, setEditing] = useState<EquipmentBrandRead | null>(null);
  const [deleting, setDeleting] = useState<EquipmentBrandRead | null>(null);

  async function create(name: string) {
    setCreating(false); // ✅ close immediately
    try {
      await equipmentApi.createBrand(tid, { name });
      showFlash("success", "Marque créée.");
      refresh();
    } catch (e: any) {
      showFlash("error", mapApiError(e));
    }
  }

  async function rename(name: string) {
    if (!editing) return;
    try {
      await equipmentApi.updateBrand(editing.id, { name });
      setEditing(null);
      showFlash("success", "Marque mise à jour.");
      refresh();
    } catch (e: any) {
      showFlash("error", mapApiError(e));
    }
  }

  async function remove() {
    if (!deleting) return;
    try {
      await equipmentApi.deleteBrand(deleting.id);
      setDeleting(null);
      showFlash("success", "Marque supprimée.");
      refresh();
    } catch (e: any) {
      setDeleting(null);
      showFlash("error", mapApiError(e));
    }
  }

  return (
    <div style={{ display: "grid", gap: 12 }}>
      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "end", gap: 12 }}>
        <div>
          <h2 style={{ margin: 0 }}>Équipements — Marques</h2>
          <div className="small">Type ID: {tid}</div>
        </div>

        <div style={{ display: "flex", gap: 10, alignItems: "center", flexWrap: "wrap" }}>
          <Link className="btn" to="/admin/equipment/types">← Retour Types</Link>

          {/* SEARCH CARD + CREATE BTN */}
          <div className="card" style={{ padding: 10, display: "flex", gap: 10, alignItems: "center", flexWrap: "wrap" }}>
            <input
              className="input"
              placeholder="Rechercher..."
              value={search}
              onChange={(e) => { setSearch(e.target.value); setPage(1); }}
              style={{ width: 240 }}
            />

            <button className="btn btn-primary" onClick={() => setCreating(true)}>
              Créer
            </button>

            <div className="small">Page {page}/{totalPages}</div>
            <button className="btn" onClick={() => setPage((p) => Math.max(1, p - 1))} disabled={page <= 1}>Précédent</button>
            <button className="btn" onClick={() => setPage((p) => Math.min(totalPages, p + 1))} disabled={page >= totalPages}>Suivant</button>
          </div>
        </div>
      </div>

      {/* ✅ message under search */}
      {flash && (
        <div className="small" style={{ color: flash.type === "success" ? "var(--success)" : "var(--danger)" }}>
          {flash.text}
        </div>
      )}

      {loading && <div className="small">Chargement...</div>}
      {error && <div style={{ color: "var(--danger)", fontSize: 13 }}>{error}</div>}

      {data && (
        <EquipmentBrandTable
          items={data.items}
          onEdit={(b) => setEditing(b)}
          onDelete={(b) => setDeleting(b)}
        />
      )}

      {/* Rename quick card (kept as-is, can be converted to modal later if you want) */}
      {editing && (
        <div className="card" style={{ padding: 12 }}>
          <div style={{ fontWeight: 700, marginBottom: 8 }}>Renommer la marque</div>
          <EquipmentNameForm initialName={editing.name} submitLabel="Enregistrer" onSubmit={rename} />
          <button className="btn" onClick={() => setEditing(null)}>Fermer</button>
        </div>
      )}

      {/* ✅ CREATE MODAL */}
      {creating && (
        <div
          style={{
            position: "fixed",
            inset: 0,
            zIndex: 9999,
            background: "rgba(0,0,0,0.35)",
            backdropFilter: "blur(4px)",
            WebkitBackdropFilter: "blur(4px)",
            display: "grid",
            placeItems: "center",
            padding: 16,
          }}
        >
          <div className="card" style={{ width: "100%", maxWidth: 520, padding: 16 }}>
            <div style={{ fontWeight: 700, marginBottom: 10 }}>Créer une marque</div>

            <EquipmentNameForm
              noCard
              submitLabel="Créer"
              onSubmit={create}
              actions={({ loading }) => (
                <div style={{ display: "flex", justifyContent: "center", gap: 10 }}>
                  <button className="btn btn-primary" type="submit" disabled={loading}>
                    Créer
                  </button>
                  <button className="btn" type="button" onClick={() => setCreating(false)} disabled={loading}>
                    Fermer
                  </button>
                </div>
              )}
            />
          </div>
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