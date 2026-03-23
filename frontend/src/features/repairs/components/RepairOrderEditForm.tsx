import { useState } from "react";
import { mapApiError, validateUpdateRepair } from "../repairs.validators";
import type {
  RepairOrderRead,
  UpdateRepairOrderPayload,
} from "../repairs.types";

export default function RepairOrderEditForm({
  repair,
  onSubmit,
}: {
  repair: RepairOrderRead;
  onSubmit: (payload: UpdateRepairOrderPayload) => Promise<void>;
}) {
  const [issueId] = useState<number | "">(repair.issue?.id ?? "");
  const [price, setPrice] = useState(String(repair.price ?? 0));
  const [deposit, setDeposit] = useState(
    repair.deposit === null || repair.deposit === undefined
      ? ""
      : String(repair.deposit),
  );
  const [description, setDescription] = useState(repair.description ?? "");

  const [loading, setLoading] = useState(false);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});
  const [formError, setFormError] = useState<string | null>(null);

  async function submitUpdate(e: React.FormEvent) {
    e.preventDefault();
    setFormError(null);

    const payload: UpdateRepairOrderPayload = {
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
        <div className="small">
          Équipement : {repair.equipmentModel?.name ?? "-"}
        </div>
      </div>

      <div>
        <label className="small">Panne</label>
        <input className="input" value={repair.issue?.name ?? ""} disabled />
        <div className="small" style={{ marginTop: 4, opacity: 0.7 }}>
          La modification de la panne pourra être ajoutée plus tard.
        </div>
      </div>

      <div style={{ display: "grid", gap: 12, gridTemplateColumns: "1fr 1fr" }}>
        <div>
          <label className="small">Prix (€)</label>
          <input
            className="input"
            value={price}
            onChange={(e) => setPrice(e.target.value)}
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
        />
        {fieldErrors.description && (
          <div style={{ color: "var(--danger)", fontSize: 13 }}>
            {fieldErrors.description}
          </div>
        )}
      </div>

      {formError && (
        <div style={{ color: "var(--danger)", fontSize: 13 }}>{formError}</div>
      )}

      <button className="btn btn-primary" disabled={loading}>
        {loading ? "Enregistrement..." : "Mettre à jour"}
      </button>
    </form>
  );
}
