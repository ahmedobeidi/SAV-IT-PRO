import { useEffect, useMemo, useState } from "react";
import { mapApiError, validateUpdateRepair } from "../repairs.validators";
import type {
  RepairOrderRead,
  UpdateRepairOrderPayload,
} from "../repairs.types";
import { useEquipmentCascade } from "../hooks/useEquipmentCascade";
import { useIssuesByType } from "../hooks/useIssuesByType";
import IssueManagementDialog from "./IssueManagementDialog";

export default function RepairOrderEditForm({
  repair,
  onSubmit,
}: {
  repair: RepairOrderRead;
  onSubmit: (payload: UpdateRepairOrderPayload) => Promise<void>;
}) {
  const initialTypeId =
    repair.equipmentModel?.equipmentBrand?.equipmentType?.id ?? undefined;
  const initialBrandId = repair.equipmentModel?.equipmentBrand?.id ?? undefined;
  const initialModelId = repair.equipmentModel?.id ?? undefined;

  const eq = useEquipmentCascade({
    typeId: initialTypeId,
    brandId: initialBrandId,
    modelId: initialModelId,
  });

  const issues = useIssuesByType(typeof eq.typeId === "number" ? eq.typeId : "");

  const [issueId, setIssueId] = useState<number | "">(repair.issue?.id ?? "");
  const [price, setPrice] = useState(String(repair.price ?? 0));
  const [deposit, setDeposit] = useState(
    repair.deposit === null || repair.deposit === undefined
      ? ""
      : String(repair.deposit),
  );
  const [description, setDescription] = useState(repair.description ?? "");

  const [showIssueManager, setShowIssueManager] = useState(false);

  const [loading, setLoading] = useState(false);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});
  const [formError, setFormError] = useState<string | null>(null);

  const equipmentModelId = useMemo(
    () => (eq.modelId ? Number(eq.modelId) : 0),
    [eq.modelId],
  );

  useEffect(() => {
    setIssueId("");
  }, [eq.typeId]);

  useEffect(() => {
    if (!repair.issue?.id) return;
    if (!issues.items.length) return;

    const exists = issues.items.some((i) => i.id === repair.issue.id);
    if (exists && issueId === "") {
      setIssueId(repair.issue.id);
    }
  }, [issues.items, repair.issue?.id, issueId]);

  async function submitUpdate(e: React.FormEvent) {
    e.preventDefault();
    setFormError(null);

    const payload: UpdateRepairOrderPayload = {
      equipmentModelId,
      issueId: issueId ? Number(issueId) : 0,
      price: Number(price),
      deposit: deposit === "" ? null : Number(deposit),
      description: description || null,
    };

    const errs = validateUpdateRepair(payload);
    setFieldErrors(errs);
    if (Object.keys(errs).length) return;

    setLoading(true);
    try {
      await onSubmit(payload);
    } catch (e: any) {
      setFormError(mapApiError(e));
    } finally {
      setLoading(false);
    }
  }

  return (
    <>
      <form onSubmit={submitUpdate} style={{ display: "grid", gap: 12 }}>
        <div
          style={{
            background: "var(--bg-soft)",
            padding: 12,
            borderRadius: 6,
            display: "grid",
            gap: 6,
          }}
        >
          <div style={{ fontWeight: 700 }}>
            Client : {repair.createdFor.lastName} {repair.createdFor.firstName}
          </div>
          <div className="small">{repair.createdFor.phone}</div>
          <div className="small">Référence : {repair.reference}</div>
        </div>

        {eq.error && (
          <div style={{ color: "var(--danger)", fontSize: 13 }}>
            {eq.error}
          </div>
        )}

        <div
          style={{
            display: "grid",
            gap: 12,
            gridTemplateColumns: "1fr 1fr 1fr",
          }}
        >
          <div>
            <label className="small">Type</label>
            <select
              className="input"
              value={eq.typeId}
              onChange={(e) =>
                eq.setTypeId(e.target.value ? Number(e.target.value) : "")
              }
              disabled={eq.loadingTypes || loading}
            >
              <option value="">— Choisir —</option>
              {eq.types.map((t) => (
                <option key={t.id} value={t.id}>
                  {t.name}
                </option>
              ))}
            </select>
          </div>

          <div>
            <label className="small">Marque</label>
            <select
              className="input"
              value={eq.brandId}
              onChange={(e) =>
                eq.setBrandId(e.target.value ? Number(e.target.value) : "")
              }
              disabled={!eq.typeId || eq.loadingBrands || loading}
            >
              <option value="">
                {eq.typeId
                  ? eq.loadingBrands
                    ? "Chargement..."
                    : "— Choisir —"
                  : "Choisis d'abord un type"}
              </option>
              {eq.brands.map((b) => (
                <option key={b.id} value={b.id}>
                  {b.name}
                </option>
              ))}
            </select>
          </div>

          <div>
            <label className="small">Modèle</label>
            <select
              className="input"
              value={eq.modelId}
              onChange={(e) =>
                eq.setModelId(e.target.value ? Number(e.target.value) : "")
              }
              disabled={!eq.brandId || eq.loadingModels || loading}
            >
              <option value="">
                {eq.brandId
                  ? eq.loadingModels
                    ? "Chargement..."
                    : "— Choisir —"
                  : "Choisis d'abord une marque"}
              </option>
              {eq.models.map((m) => (
                <option key={m.id} value={m.id}>
                  {m.name}
                </option>
              ))}
            </select>

            {fieldErrors.equipmentModelId && (
              <div style={{ color: "var(--danger)", fontSize: 13 }}>
                {fieldErrors.equipmentModelId}
              </div>
            )}
          </div>
        </div>

        {issues.error && (
          <div style={{ color: "var(--danger)", fontSize: 13 }}>
            {issues.error}
          </div>
        )}

        <div>
          <label className="small">Panne</label>
          <div style={{ display: "flex", gap: 8, alignItems: "flex-start" }}>
            <select
              className="input"
              value={issueId}
              onChange={(e) =>
                setIssueId(e.target.value ? Number(e.target.value) : "")
              }
              disabled={!eq.typeId || issues.loading || loading}
              style={{ flex: 1 }}
            >
              <option value="">
                {!eq.typeId
                  ? "Choisis d'abord un type"
                  : issues.loading
                    ? "Chargement..."
                    : "— Choisir —"}
              </option>

              {issues.items.map((i) => (
                <option key={i.id} value={i.id}>
                  {i.name}
                </option>
              ))}
            </select>

            <button
              type="button"
              className="btn btn-primary"
              onClick={() => setShowIssueManager(true)}
              disabled={!eq.typeId || loading}
            >
              ⚙️ Gérer
            </button>
          </div>

          {fieldErrors.issueId && (
            <div style={{ color: "var(--danger)", fontSize: 13 }}>
              {fieldErrors.issueId}
            </div>
          )}
        </div>

        <div
          style={{
            display: "grid",
            gap: 12,
            gridTemplateColumns: "1fr 1fr",
          }}
        >
          <div>
            <label className="small">Prix (€)</label>
            <input
              className="input"
              value={price}
              onChange={(e) => setPrice(e.target.value)}
              disabled={loading}
            />
            {fieldErrors.price && (
              <div style={{ color: "var(--danger)", fontSize: 13 }}>
                {fieldErrors.price}
              </div>
            )}
          </div>

          <div>
            <label className="small">Acompte (€)</label>
            <input
              className="input"
              value={deposit}
              onChange={(e) => setDeposit(e.target.value)}
              disabled={loading}
            />
            {fieldErrors.deposit && (
              <div style={{ color: "var(--danger)", fontSize: 13 }}>
                {fieldErrors.deposit}
              </div>
            )}
          </div>
        </div>

        <div>
          <label className="small">Description</label>
          <textarea
            className="input"
            value={description}
            onChange={(e) => setDescription(e.target.value)}
            style={{ minHeight: 110, resize: "vertical" }}
            disabled={loading}
          />
          {fieldErrors.description && (
            <div style={{ color: "var(--danger)", fontSize: 13 }}>
              {fieldErrors.description}
            </div>
          )}
        </div>

        {formError && (
          <div style={{ color: "var(--danger)", fontSize: 13 }}>
            {formError}
          </div>
        )}

        <button className="btn btn-primary" disabled={loading}>
          {loading ? "Enregistrement..." : "Mettre à jour"}
        </button>
      </form>

      <IssueManagementDialog
        open={showIssueManager}
        onClose={() => setShowIssueManager(false)}
        typeId={eq.typeId || 0}
        issues={issues.items}
        onIssueSelected={(issue) => {
          setIssueId(issue.id);
          setShowIssueManager(false);
        }}
        onRefresh={issues.refresh || (() => {})}
      />
    </>
  );
}