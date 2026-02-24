import { useState } from "react";
import { validateName } from "../equipment.validators";

export default function EquipmentNameForm({
  initialName = "",
  submitLabel,
  onSubmit,
  noCard,
  actions,
}: {
  initialName?: string;
  submitLabel: string;
  onSubmit: (name: string) => Promise<void>;
  noCard?: boolean;
  actions?: (opts: { loading: boolean }) => React.ReactNode;
}) {
  const [name, setName] = useState(initialName);
  const [loading, setLoading] = useState(false);
  const [fieldError, setFieldError] = useState<string | null>(null);

  async function submit(e: React.FormEvent) {
    e.preventDefault();

    const err = validateName(name);
    setFieldError(err);
    if (err) return;

    setLoading(true);
    try {
      await onSubmit(name.trim());
      setName("");
    } finally {
      setLoading(false);
    }
  }

  return (
    <form
      onSubmit={submit}
      className={noCard ? undefined : "card"}
      style={{
        padding: noCard ? 0 : 12,
        display: "flex",
        flexDirection: "column",
        gap: 12,
      }}
    >
      <div>
        <label className="small">Nom</label>
        <input
          className="input"
          value={name}
          onChange={(e) => setName(e.target.value)}
          placeholder="ex: Smartphone"
        />
        {fieldError && (
          <div style={{ color: "var(--danger)", fontSize: 13 }}>
            {fieldError}
          </div>
        )}
      </div>

      {actions
        ? actions({ loading })
        : (
          <div style={{ display: "flex", justifyContent: "center" }}>
            <button className="btn btn-primary" type="submit" disabled={loading}>
              {submitLabel}
            </button>
          </div>
        )
      }
    </form>
  );
}