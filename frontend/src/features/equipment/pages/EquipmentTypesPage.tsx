import { useMemo, useState } from "react";
import { equipmentApi } from "../equipment.api";
import { useEquipmentTypes } from "../hooks/useEquipmentTypes";
import EquipmentNameForm from "../components/EquipmentNameForm";
import EquipmentTypeTable from "../components/EquipmentTypeTable";
import ConfirmDialog from "../../../shared/components/ConfirmDialog/ConfirmDialog";
import BottomPagination from "../../../shared/pagination/BottomPagination";
import { useFlashMessage } from "../../../shared/flash/useFlashMessage";
import type { EquipmentTypeRead } from "../equipment.types";
import { mapCrudApiError } from "../../../shared/errors/mapCrudApiError";

export default function EquipmentTypesPage() {
  const [search, setSearch] = useState("");
  const [page, setPage] = useState(1);
  const limit = 10;

  const { data, loading, error, refresh } = useEquipmentTypes(
    search,
    page,
    limit,
  );

  const totalPages = useMemo(() => {
    if (!data) return 1;
    return Math.max(1, Math.ceil(data.total / data.limit));
  }, [data]);
  const { flash, showFlash } = useFlashMessage();

  const [editing, setEditing] = useState<EquipmentTypeRead | null>(null);
  const [deleting, setDeleting] = useState<EquipmentTypeRead | null>(null);
  const [creating, setCreating] = useState(false);

  async function create(name: string) {
    setCreating(false);
    try {
      await equipmentApi.createType({ name });
      showFlash("success", "Type créé avec succès.");
      refresh();
    } catch (e: any) {
      showFlash("error", mapCrudApiError(e, { conflictMessage: "Conflit: existe déjà ou suppression interdite." }));
    }
  }

  async function rename(name: string) {
    if (!editing) return;

    const current = editing;
    setEditing(null);

    try {
      await equipmentApi.updateType(current.id, { name });
      showFlash("success", "Type mis à jour.");
      refresh();
    } catch (e: any) {
      showFlash("error", mapCrudApiError(e, { conflictMessage: "Conflit: existe déjà ou suppression interdite." }));
    }
  }

  async function remove() {
    if (!deleting) return;

    const current = deleting;
    setDeleting(null);

    try {
      await equipmentApi.deleteType(current.id);
      showFlash("success", "Type supprimé.");
      refresh();
    } catch (e: any) {
      showFlash("error", mapCrudApiError(e, { conflictMessage: "Conflit: existe déjà ou suppression interdite." }));
    }
  }

  return (
    <div className="page-stack">
      <div className="page-header">
        <div>
          <h2 className="page-title">Types</h2>
        </div>

        <button className="btn btn-primary" onClick={() => setCreating(true)}>
          Créer
        </button>
      </div>

      <div className="card page-toolbar">
        <div className="page-toolbar-group">
          <input
            className="input page-search-input"
            placeholder="Rechercher par nom..."
            value={search}
            onChange={(e) => {
              setSearch(e.target.value);
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
          <EquipmentTypeTable
            items={data.items}
            onEdit={(t) => setEditing(t)}
            onDelete={(t) => setDeleting(t)}
          />

          <BottomPagination
            page={page}
            totalPages={totalPages}
            onChange={setPage}
          />
        </>
      )}

      {editing && (
        <div className="overlay-backdrop">
          <div className="card overlay-card">
            <div className="overlay-title">
              Renommer le type
            </div>

            <EquipmentNameForm
              noCard
              initialName={editing.name}
              submitLabel="Enregistrer"
              onSubmit={rename}
              actions={({ loading }) => (
                <div className="modal-actions center-text">
                  <button
                    className="btn"
                    type="button"
                    onClick={() => setEditing(null)}
                    disabled={loading}
                  >
                    Fermer
                  </button>

                  <button
                    className="btn btn-primary"
                    type="submit"
                    disabled={loading}
                  >
                    Enregistrer
                  </button>
                </div>
              )}
            />
          </div>
        </div>
      )}

      {creating && (
        <div className="overlay-backdrop">
          <div className="card overlay-card">
            <div className="overlay-title">
              Créer un type
            </div>

            <EquipmentNameForm
              noCard
              submitLabel="Créer"
              onSubmit={create}
              actions={({ loading }) => (
                <div className="modal-actions center-text">
                  <button
                    className="btn"
                    type="button"
                    onClick={() => setCreating(false)}
                    disabled={loading}
                  >
                    Fermer
                  </button>

                  <button
                    className="btn btn-primary"
                    type="submit"
                    disabled={loading}
                  >
                    Créer
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
