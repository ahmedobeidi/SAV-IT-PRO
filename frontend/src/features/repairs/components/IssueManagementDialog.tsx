import { useState } from "react";
import { Pencil, Trash2, Plus } from "lucide-react";
import { issuesApi, type IssueRead } from "../../issues/issues.api";

export default function IssueManagementDialog({
  open,
  onClose,
  typeId: equipmentTypeId,
  issues,
  onIssueSelected,
  onRefresh,
}: {
  open: boolean;
  onClose: () => void;
  typeId: number;
  issues: IssueRead[];
  onIssueSelected: (issue: IssueRead) => void;
  onRefresh: () => void;
}) {
  const [mode, setMode] = useState<"list" | "create" | "edit" | "delete">(
    "list",
  );
  const [selectedIssue, setSelectedIssue] = useState<IssueRead | null>(null);
  const [issueName, setIssueName] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  if (!open) return null;

  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault();
    const name = issueName.trim();
    if (!name) {
      setError("Le nom de la panne ne peut pas être vide.");
      return;
    }

    setLoading(true);
    setError(null);
    try {
      const newIssue = await issuesApi.create(equipmentTypeId, { name });
      setIssueName("");
      setMode("list");
      await onRefresh();
      onIssueSelected(newIssue);
    } catch (e: any) {
      setError(e?.response?.data?.message || "Erreur lors de la création.");
    } finally {
      setLoading(false);
    }
  };

  const handleUpdate = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedIssue) return;

    const name = issueName.trim();
    if (!name) {
      setError("Le nom de la panne ne peut pas être vide.");
      return;
    }

    setLoading(true);
    setError(null);
    try {
      await issuesApi.update(selectedIssue.id, { name });
      setIssueName("");
      setSelectedIssue(null);
      setMode("list");
      await onRefresh();
    } catch (e: any) {
      setError(e?.response?.data?.message || "Erreur lors de la mise à jour.");
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async () => {
    if (!selectedIssue) return;

    setLoading(true);
    setError(null);
    try {
      await issuesApi.delete(selectedIssue.id);
      setSelectedIssue(null);
      setMode("list");
      await onRefresh();
    } catch (e: any) {
      setError(e?.response?.data?.message || "Erreur lors de la suppression.");
    } finally {
      setLoading(false);
    }
  };

  // LIST MODE
  if (mode === "list") {
    return (
      <div
        style={{
          position: "fixed",
          inset: 0,
          zIndex: 9999,
          background: "var(--overlay-soft)",
          backdropFilter: "blur(4px)",
          WebkitBackdropFilter: "blur(4px)",
          display: "grid",
          placeItems: "center",
          padding: 16,
        }}
      >
        <div
          className="card"
          style={{ width: "100%", maxWidth: 520, padding: 16 }}
        >
          <div style={{ fontWeight: 700, marginBottom: 16 }}>
            Gérer les pannes
          </div>

          <div style={{ maxHeight: 300, overflowY: "auto", marginBottom: 16 }}>
            {issues.length === 0 ? (
              <div className="small" style={{ color: "var(--text-muted)" }}>
                Aucune panne disponible.
              </div>
            ) : (
              <div style={{ display: "grid", gap: 8 }}>
                {issues.map((issue) => (
                  <div
                    key={issue.id}
                    style={{
                      display: "flex",
                      justifyContent: "space-between",
                      alignItems: "center",
                      padding: 10,
                      backgroundColor: "var(--panel-2)",
                      borderRadius: 4,
                    }}
                  >
                    <span>{issue.name}</span>
                    <div style={{ display: "flex", gap: 6 }}>
                      <button
                        className="btn"
                        onClick={() => {
                          setSelectedIssue(issue);
                          setIssueName(issue.name);
                          setMode("edit");
                        }}
                        title="Modifier"
                        style={{ padding: 4 }}
                      >
                        <Pencil size={16} />
                      </button>
                      <button
                        className="btn btn-danger"
                        onClick={() => {
                          setSelectedIssue(issue);
                          setMode("delete");
                        }}
                        title="Supprimer"
                        style={{ padding: 4 }}
                      >
                        <Trash2 size={16} />
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>

          <div style={{ display: "flex", gap: 10, justifyContent: "end" }}>
            <button className="btn" onClick={onClose}>
              Fermer
            </button>
            <button
              className="btn btn-primary"
              onClick={() => {
                setIssueName("");
                setMode("create");
              }}
              style={{
                display: "flex",
                alignItems: "center",
                gap: 3,
              }}
            >
              <Plus size={18} /> Créer
            </button>
          </div>
        </div>
      </div>
    );
  }

  // CREATE MODE
  if (mode === "create") {
    return (
      <div
        style={{
          position: "fixed",
          inset: 0,
          zIndex: 10000,
          background: "var(--overlay-soft)",
          backdropFilter: "blur(4px)",
          WebkitBackdropFilter: "blur(4px)",
          display: "grid",
          placeItems: "center",
          padding: 16,
        }}
      >
        <div
          className="card"
          style={{ width: "100%", maxWidth: 520, padding: 16 }}
        >
          <div style={{ fontWeight: 700, marginBottom: 16 }}>
            Créer une panne
          </div>

          <form onSubmit={handleCreate} style={{ display: "grid", gap: 12 }}>
            <div>
              <label className="small">Nom de la panne</label>
              <input
                className="input"
                placeholder="ex: Écran cassé"
                value={issueName}
                onChange={(e) => setIssueName(e.target.value)}
                disabled={loading}
              />
            </div>

            {error && (
              <div style={{ color: "var(--danger)", fontSize: 13 }}>
                {error}
              </div>
            )}

            <div style={{ display: "flex", justifyContent: "center", gap: 10 }}>
              <button
                type="button"
                className="btn"
                onClick={() => {
                  setMode("list");
                  setError(null);
                }}
                disabled={loading}
              >
                Retour
              </button>

              <button
                type="submit"
                className="btn btn-primary"
                disabled={loading}
              >
                {loading ? "Création..." : "Créer"}
              </button>
            </div>
          </form>
        </div>
      </div>
    );
  }

  // EDIT MODE
  if (mode === "edit") {
    return (
      <div
        style={{
          position: "fixed",
          inset: 0,
          zIndex: 10000,
          background: "var(--overlay-soft)",
          backdropFilter: "blur(4px)",
          WebkitBackdropFilter: "blur(4px)",
          display: "grid",
          placeItems: "center",
          padding: 16,
        }}
      >
        <div
          className="card"
          style={{ width: "100%", maxWidth: 520, padding: 16 }}
        >
          <div style={{ fontWeight: 700, marginBottom: 16 }}>
            Modifier la panne
          </div>

          <form onSubmit={handleUpdate} style={{ display: "grid", gap: 12 }}>
            <div>
              <label className="small">Nom de la panne</label>
              <input
                className="input"
                value={issueName}
                onChange={(e) => setIssueName(e.target.value)}
                disabled={loading}
              />
            </div>

            {error && (
              <div style={{ color: "var(--danger)", fontSize: 13 }}>
                {error}
              </div>
            )}

            <div style={{ display: "flex", justifyContent: "center", gap: 10 }}>
              <button
                type="button"
                className="btn"
                onClick={() => {
                  setMode("list");
                  setError(null);
                }}
                disabled={loading}
              >
                Retour
              </button>

              <button
                type="submit"
                className="btn btn-primary"
                disabled={loading}
              >
                {loading ? "Enregistrement..." : "Enregistrer"}
              </button>
            </div>
          </form>
        </div>
      </div>
    );
  }

  // DELETE MODE
  if (mode === "delete") {
    return (
      <div
        style={{
          position: "fixed",
          inset: 0,
          zIndex: 10000,
          background: "var(--overlay-soft)",
          backdropFilter: "blur(4px)",
          WebkitBackdropFilter: "blur(4px)",
          display: "grid",
          placeItems: "center",
          padding: 16,
        }}
      >
        <div
          className="card"
          style={{ width: "100%", maxWidth: 520, padding: 16 }}
        >
          <div
            style={{
              fontWeight: 700,
              marginBottom: 16,
              color: "var(--danger)",
            }}
          >
            Supprimer la panne
          </div>

          <p style={{ marginBottom: 16 }}>
            Êtes-vous sûr de vouloir supprimer la panne{" "}
            <strong>"{selectedIssue?.name}"</strong> ?
          </p>

          {error && (
            <div
              style={{ color: "var(--danger)", fontSize: 13, marginBottom: 16 }}
            >
              {error}
            </div>
          )}

          <div style={{ display: "flex", justifyContent: "center", gap: 10 }}>
            <button
              className="btn"
              onClick={() => {
                setMode("list");
                setError(null);
              }}
              disabled={loading}
            >
              Annuler
            </button>

            <button
              className="btn btn-danger"
              disabled={loading}
              onClick={handleDelete}
            >
              {loading ? "Suppression..." : "Supprimer"}
            </button>
          </div>
        </div>
      </div>
    );
  }

  return null;
}
