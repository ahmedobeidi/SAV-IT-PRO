import { useMemo, useState } from "react";
import { validateCreateRepair, mapApiError } from "../repairs.validators";
import type { CreateRepairOrderPayload } from "../repairs.types";
import { useClientSearchByPhone } from "../hooks/useClientSearchByPhone";
import { useEquipmentCascade } from "../hooks/useEquipmentCascade";

// OPTIONAL: if you have issues endpoint
// import { issuesApi, type IssueRead } from "../../issues/issues.api";

export default function RepairOrderCreateForm({
  onSubmit,
}: {
  onSubmit: (payload: CreateRepairOrderPayload) => Promise<void>;
}) {
  // --- client search
  const [phone, setPhone] = useState("");
  const { loading: clientLoading, client, error: clientError, search: searchClient, reset: resetClient } =
    useClientSearchByPhone();

  // selected clientId from search result (locked)
  const clientId = client?.id ?? 0;

  // --- equipment cascade
  const eq = useEquipmentCascade();

  // --- issues: choose ONE path
  // A) Real select (if you have endpoint)
  // const [issues, setIssues] = useState<IssueRead[]>([]);
  // const [issueId, setIssueId] = useState<number | "">("");
  // useEffect(() => {
  //   issuesApi.list().then((res) => setIssues(res.items ?? (res as any)));
  // }, []);

  // B) Temporary input id (works today)
  const [issueIdInput, setIssueIdInput] = useState("");

  // --- form fields
  const [price, setPrice] = useState("0");
  const [deposit, setDeposit] = useState("");
  const [description, setDescription] = useState("");

  const [loading, setLoading] = useState(false);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});
  const [formError, setFormError] = useState<string | null>(null);

  const equipmentModelId = eq.modelId ? Number(eq.modelId) : 0;

  // Issue id for payload (choose based on A or B)
  const issueId = useMemo(() => {
    // A) return issueId ? Number(issueId) : 0;
    // B)
    return Number(issueIdInput);
  }, [issueIdInput]);

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    setFormError(null);

    const payload: CreateRepairOrderPayload = {
      clientId,
      equipmentModelId,
      issueId,
      price: Number(price),
      deposit: deposit === "" ? null : Number(deposit),
      description: description || null,
    };

    const errs = validateCreateRepair(payload);
    setFieldErrors(errs);
    if (Object.keys(errs).length) return;

    setLoading(true);
    try {
      await onSubmit(payload);
    } catch (e: any) {
      setFormError(mapApiError(e));
      throw e;
    } finally {
      setLoading(false);
    }
  }

  return (
    <form onSubmit={submit} className="card" style={{ padding: 16, display: "grid", gap: 12, maxWidth: 860 }}>
      {/* CLIENT */}
      <div style={{ fontWeight: 700 }}>1) Client</div>

      <div className="card" style={{ padding: 12, display: "grid", gap: 10 }}>
        <div style={{ display: "flex", gap: 10, flexWrap: "wrap", alignItems: "end" }}>
          <div style={{ minWidth: 260 }}>
            <label className="small">Téléphone (recherche)</label>
            <input
              className="input"
              placeholder="ex: 0601020304"
              value={phone}
              onChange={(e) => setPhone(e.target.value)}
            />
          </div>

          <button
            type="button"
            className="btn"
            onClick={() => searchClient(phone)}
            disabled={clientLoading}
          >
            {clientLoading ? "Recherche..." : "Rechercher"}
          </button>

          <button
            type="button"
            className="btn"
            onClick={() => {
              setPhone("");
              resetClient();
            }}
          >
            Réinitialiser
          </button>
        </div>

        {clientError && <div style={{ color: "var(--danger)", fontSize: 13 }}>{clientError}</div>}

        <div className="small">
          {client ? (
            <>
              Client sélectionné: <b>{client.lastName} {client.firstName}</b> — {client.phone} (ID: {client.id})
            </>
          ) : (
            "Aucun client sélectionné."
          )}
        </div>

        {fieldErrors.clientId && <div style={{ color: "var(--danger)", fontSize: 13 }}>{fieldErrors.clientId}</div>}
      </div>

      {/* EQUIPMENT */}
      <div style={{ fontWeight: 700 }}>2) Équipement</div>

      {eq.error && <div style={{ color: "var(--danger)", fontSize: 13 }}>{eq.error}</div>}

      <div style={{ display: "grid", gap: 12, gridTemplateColumns: "1fr 1fr 1fr" }}>
        <div>
          <label className="small">Type</label>
          <select
            className="input"
            value={eq.typeId}
            onChange={(e) => eq.setTypeId(e.target.value ? Number(e.target.value) : "")}
            disabled={eq.loadingTypes}
          >
            <option value="">— Choisir —</option>
            {eq.types.map((t) => (
              <option key={t.id} value={t.id}>{t.name}</option>
            ))}
          </select>
        </div>

        <div>
          <label className="small">Marque</label>
          <select
            className="input"
            value={eq.brandId}
            onChange={(e) => eq.setBrandId(e.target.value ? Number(e.target.value) : "")}
            disabled={!eq.typeId || eq.loadingBrands}
          >
            <option value="">
              {eq.typeId ? (eq.loadingBrands ? "Chargement..." : "— Choisir —") : "Choisis d’abord un type"}
            </option>
            {eq.brands.map((b) => (
              <option key={b.id} value={b.id}>{b.name}</option>
            ))}
          </select>
        </div>

        <div>
          <label className="small">Modèle</label>
          <select
            className="input"
            value={eq.modelId}
            onChange={(e) => eq.setModelId(e.target.value ? Number(e.target.value) : "")}
            disabled={!eq.brandId || eq.loadingModels}
          >
            <option value="">
              {eq.brandId ? (eq.loadingModels ? "Chargement..." : "— Choisir —") : "Choisis d’abord une marque"}
            </option>
            {eq.models.map((m) => (
              <option key={m.id} value={m.id}>{m.name}</option>
            ))}
          </select>

          {fieldErrors.equipmentModelId && (
            <div style={{ color: "var(--danger)", fontSize: 13 }}>{fieldErrors.equipmentModelId}</div>
          )}
        </div>
      </div>

      {/* ISSUE */}
      <div style={{ fontWeight: 700 }}>3) Panne</div>

      {/* A) If you have issues list, replace this by a select */}
      <div>
        <label className="small">Issue ID (temporaire)</label>
        <input className="input" value={issueIdInput} onChange={(e) => setIssueIdInput(e.target.value)} />
        {fieldErrors.issueId && <div style={{ color: "var(--danger)", fontSize: 13 }}>{fieldErrors.issueId}</div>}
        <div className="small" style={{ marginTop: 6 }}>
          (Dès que tu exposes <b>GET /api/issues</b>, je te mets le select propre.)
        </div>
      </div>

      {/* PRICE / DEPOSIT / DESCRIPTION */}
      <div style={{ fontWeight: 700 }}>4) Détails</div>

      <div style={{ display: "grid", gap: 12, gridTemplateColumns: "1fr 1fr" }}>
        <div>
          <label className="small">Prix (€)</label>
          <input className="input" value={price} onChange={(e) => setPrice(e.target.value)} />
          {fieldErrors.price && <div style={{ color: "var(--danger)", fontSize: 13 }}>{fieldErrors.price}</div>}
        </div>

        <div>
          <label className="small">Acompte (€)</label>
          <input className="input" value={deposit} onChange={(e) => setDeposit(e.target.value)} />
          {fieldErrors.deposit && <div style={{ color: "var(--danger)", fontSize: 13 }}>{fieldErrors.deposit}</div>}
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
        {fieldErrors.description && <div style={{ color: "var(--danger)", fontSize: 13 }}>{fieldErrors.description}</div>}
      </div>

      {formError && <div style={{ color: "var(--danger)", fontSize: 13 }}>{formError}</div>}

      <button className="btn btn-primary" disabled={loading}>
        {loading ? "Création..." : "Créer l’ordre de réparation"}
      </button>
    </form>
  );
}