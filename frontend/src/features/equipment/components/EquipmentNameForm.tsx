import { useState } from "react";
import { validateName } from "../equipment.validators";

export default function EquipmentNameForm({
  initialName = "",
  submitLabel,
  onSubmit,
}: {
  initialName?: string;
  submitLabel: string;
  onSubmit: (name: string) => Promise<void>;
}) {
  const [name, setName] = useState(initialName);
  const [loading, setLoading] = useState(false);
  const [fieldError, setFieldError] = useState<string | null>(null);
  const [formError, setFormError] = useState<string | null>(null);

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    setFormError(null);

    const err = validateName(name);
    setFieldError(err);
    if (err) return;

    setLoading(true);
    try {
      await onSubmit(name.trim());
      setName("");
    } catch (e: any) {
      // on laisse la page mapper le message si besoin
      setFormError("Action impossible (droits / validation / conflit).");
    } finally {
      setLoading(false);
    }
  }

  return (
    <form onSubmit={submit} className="card" style={{ padding: 12, display: "flex", gap: 10, alignItems: "end", flexWrap: "wrap" }}>
      <div style={{ flex: 1, minWidth: 240 }}>
        <label className="small">Nom</label>
        <input className="input" value={name} onChange={(e) => setName(e.target.value)} placeholder="ex: Smartphone" />
        {fieldError && <div style={{ color: "var(--danger)", fontSize: 13 }}>{fieldError}</div>}
      </div>

      <button className="btn btn-primary" disabled={loading}>
        {loading ? "..." : submitLabel}
      </button>

      {formError && <div style={{ color: "var(--danger)", fontSize: 13, width: "100%" }}>{formError}</div>}
    </form>
  );
}