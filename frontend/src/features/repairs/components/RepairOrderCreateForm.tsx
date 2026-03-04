import { useMemo, useState } from "react";
import { validateCreateRepair, mapApiError } from "../repairs.validators";
import type { CreateRepairOrderPayload } from "../repairs.types";
import { useEquipmentCascade } from "../hooks/useEquipmentCascade";
import { useClientSearchList } from "../hooks/useClientSearchList";
import type { ClientRead } from "../../clients/clients.types";

// OPTIONAL: if you have issues endpoint
// import { issuesApi, type IssueRead } from "../../issues/issues.api";

export default function RepairOrderCreateForm({
  onSubmit,
}: {
  onSubmit: (payload: CreateRepairOrderPayload) => Promise<void>;
}) {
  // --- STEP 1: client search + select
  const [phone, setPhone] = useState("");
  const [selectedClient, setSelectedClient] = useState<ClientRead | null>(null);

  const { loading: clientLoading, items: clientsFound, error: clientError } =
    useClientSearchList(phone);

  const clientId = selectedClient?.id ?? 0;

  // --- STEP 2: equipment cascade
  const eq = useEquipmentCascade();

  // --- issues: choose ONE path
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

  // Issue id for payload
  const issueId = useMemo(() => Number(issueIdInput), [issueIdInput]);

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    setFormError(null);

    if (!selectedClient) {
      setFormError("Sélectionne un client avant de créer l’ordre.");
      return;
    }

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
    <form
      onSubmit={submit}
      className="card"
      style={{ padding: 16, display: "grid", gap: 12, maxWidth: 860 }}
    >
      {/* STEP 1 — Search Client */}
      <div style={{ fontWeight: 700 }}>1) Rechercher un client</div>

      <div className="card" style={{ padding: 16, display: "grid", gap: 12 }}>
        <div style={{ display: "flex", gap: 12, flexWrap: "wrap" }}>
          <div style={{ flex: 1, minWidth: 260 }}>
            <label className="small">Téléphone</label>
            <input
              className="input"
              placeholder="Rechercher par téléphone..."
              value={phone}
              onChange={(e) => {
                setPhone(e.target.value);
                setSelectedClient(null); // reset selection on new search
                setFormError(null);
              }}
            />
            <div className="small" style={{ marginTop: 6 }}>
              {clientLoading
                ? "Recherche en cours..."
                : phone.trim().length
                ? "Recherche automatique"
                : "Tape un numéro pour rechercher"}
            </div>
          </div>
        </div>

        {clientError && (
          <div style={{ color: "var(--danger)", fontSize: 13 }}>
            {clientError}
          </div>
        )}

        {/* Results list */}
        {!selectedClient && clientsFound.length > 0 && (
          <div style={{ display: "grid", gap: 8 }}>
            <div className="small" style={{ opacity: 0.8 }}>
              Sélectionne un client :
            </div>

            {clientsFound.map((c) => (
              <button
                key={c.id}
                type="button"
                className="btn"
                style={{
                  justifyContent: "space-between",
                  display: "flex",
                  width: "100%",
                  textAlign: "left",
                }}
                onClick={() => {
                  setSelectedClient(c);
                  setFormError(null);
                }}
              >
                <span>
                  <b>
                    {c.lastName} {c.firstName}
                  </b>
                  <span className="small" style={{ marginLeft: 8, opacity: 0.8 }}>
                    📞 {c.phone}
                  </span>
                </span>
                <span className="small" style={{ opacity: 0.8 }}>
                  ID: {c.id}
                </span>
              </button>
            ))}
          </div>
        )}

        {/* Selected client */}
        {selectedClient && (
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
              ✅ Client sélectionné : {selectedClient.lastName}{" "}
              {selectedClient.firstName}
            </div>
            <div className="small">
              📞 {selectedClient.phone} — ID: {selectedClient.id}
            </div>

            <div style={{ display: "flex", gap: 10, flexWrap: "wrap" }}>
              <button
                type="button"
                className="btn"
                onClick={() => setSelectedClient(null)}
              >
                Changer
              </button>
            </div>
          </div>
        )}

        {!selectedClient && phone.trim().length >= 10 && !clientLoading && clientsFound.length === 0 && !clientError && (
          <div className="small" style={{ opacity: 0.7 }}>
            Aucun client trouvé.
          </div>
        )}
      </div>

      {/* STEP 2 — Form (only after selecting a client) */}
      {!selectedClient ? (
        <div className="small" style={{ opacity: 0.7 }}>
          Sélectionne un client pour afficher le formulaire.
        </div>
      ) : (
        <>
          {/* EQUIPMENT */}
          <div style={{ fontWeight: 700 }}>2) Équipement</div>

          {eq.error && (
            <div style={{ color: "var(--danger)", fontSize: 13 }}>{eq.error}</div>
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
                disabled={eq.loadingTypes}
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
                disabled={!eq.typeId || eq.loadingBrands}
              >
                <option value="">
                  {eq.typeId
                    ? eq.loadingBrands
                      ? "Chargement..."
                      : "— Choisir —"
                    : "Choisis d’abord un type"}
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
                disabled={!eq.brandId || eq.loadingModels}
              >
                <option value="">
                  {eq.brandId
                    ? eq.loadingModels
                      ? "Chargement..."
                      : "— Choisir —"
                    : "Choisis d’abord une marque"}
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

          {/* ISSUE */}
          <div style={{ fontWeight: 700 }}>3) Panne</div>

          <div>
            <label className="small">Issue ID (temporaire)</label>
            <input
              className="input"
              value={issueIdInput}
              onChange={(e) => setIssueIdInput(e.target.value)}
            />
            {fieldErrors.issueId && (
              <div style={{ color: "var(--danger)", fontSize: 13 }}>
                {fieldErrors.issueId}
              </div>
            )}
            <div className="small" style={{ marginTop: 6 }}>
              (Dès que tu exposes <b>GET /api/issues</b>, je te mets le select
              propre.)
            </div>
          </div>

          {/* DETAILS */}
          <div style={{ fontWeight: 700 }}>4) Détails</div>

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
            <div style={{ color: "var(--danger)", fontSize: 13 }}>
              {formError}
            </div>
          )}

          <button className="btn btn-primary" disabled={loading}>
            {loading ? "Création..." : "Créer l’ordre de réparation"}
          </button>
        </>
      )}
    </form>
  );
}