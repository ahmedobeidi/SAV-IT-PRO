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
  if (s === 409)
    return e?.response?.data?.message ??
      "Conflit: existe déjà ou suppression interdite.";
  if (s === 422) return "Validation échouée.";
  return "Erreur serveur.";
}

export default function EquipmentTypesPage() {
  const [search, setSearch] = useState("");
  const [page, setPage] = useState(1);
  const limit = 20;

  const { data, loading, error, refresh } = useEquipmentTypes(search, page, limit);

  const totalPages = useMemo(() => {
    if (!data) return 1;
    return Math.max(1, Math.ceil(data.total / data.limit));
  }, [data]);

  // ✅ flash message auto hide
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

  const [editing, setEditing] = useState<EquipmentTypeRead | null>(null);
  const [deleting, setDeleting] = useState<EquipmentTypeRead | null>(null);
  const [creating, setCreating] = useState(false);

  async function create(name: string) {
    setCreating(false); // ✅ CLOSE IMMEDIATELY

    try {
      await equipmentApi.createType({ name });
      showFlash("success", "Type créé avec succès.");
      refresh();
    } catch (e: any) {
      showFlash("error", mapApiError(e));
    }
  }

  async function rename(name: string) {
    if (!editing) return;
    try {
      await equipmentApi.updateType(editing.id, { name });
      setEditing(null);
      showFlash("success", "Type mis à jour.");
      refresh();
    } catch (e: any) {
      showFlash("error", mapApiError(e));
    }
  }

  async function remove() {
    if (!deleting) return;
    try {
      await equipmentApi.deleteType(deleting.id);
      setDeleting(null);
      showFlash("success", "Type supprimé.");
      refresh();
    } catch (e: any) {
      setDeleting(null);
      showFlash("error", mapApiError(e));
    }
  }

  return (
    <div style={{ display: "grid", gap: 12 }}>

      {/* HEADER */}
      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "end", gap: 12 }}>
        <div>
          <h2 style={{ margin: 0 }}>Équipements — Types</h2>
          <div className="small">Créer, renommer, supprimer (EPIC 4)</div>
        </div>

        <div className="card" style={{ padding: 10, display: "flex", gap: 10, alignItems: "center", flexWrap: "wrap" }}>
          <input
            className="input"
            placeholder="Rechercher..."
            value={search}
            onChange={(e) => { setSearch(e.target.value); setPage(1); }}
            style={{ width: 260 }}
          />

          <button className="btn btn-primary" onClick={() => setCreating(true)}>
            Créer
          </button>

          <div className="small">Page {page}/{totalPages}</div>

          <button className="btn" onClick={() => setPage(p => Math.max(1, p - 1))} disabled={page <= 1}>
            Précédent
          </button>

          <button className="btn" onClick={() => setPage(p => Math.min(totalPages, p + 1))} disabled={page >= totalPages}>
            Suivant
          </button>
        </div>
      </div>

      {/* ✅ MESSAGE UNDER SEARCH (AUTO 7s) */}
      {flash && (
        <div
          className="small"
          style={{
            color: flash.type === "success"
              ? "var(--success)"
              : "var(--danger)"
          }}
        >
          {flash.text}
        </div>
      )}

      {loading && <div className="small">Chargement...</div>}
      {error && <div style={{ color: "var(--danger)", fontSize: 13 }}>{error}</div>}

      {data && (
        <EquipmentTypeTable
          items={data.items}
          onEdit={(t) => setEditing(t)}
          onDelete={(t) => setDeleting(t)}
        />
      )}

      {editing && (
        <div className="card" style={{ padding: 12 }}>
          <div style={{ fontWeight: 700, marginBottom: 8 }}>Renommer le type</div>

          <EquipmentNameForm
            initialName={editing.name}
            submitLabel="Enregistrer"
            onSubmit={rename}
          />

          <button className="btn" onClick={() => setEditing(null)}>
            Fermer
          </button>
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
            <div style={{ fontWeight: 700, marginBottom: 10 }}>
              Créer un type
            </div>

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