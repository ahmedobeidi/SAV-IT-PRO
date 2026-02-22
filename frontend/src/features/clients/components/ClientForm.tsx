import { useState } from "react";
import type { ClientRead, CreateClientPayload, UpdateClientPayload } from "../clients.types";
import { validateCreateClient, validateUpdateClient } from "../clients.validators";

type Mode = "create" | "edit";

export default function ClientForm({
  mode,
  initial,
  onSubmit,
}: {
  mode: Mode;
  initial?: ClientRead;
  onSubmit: (payload: CreateClientPayload | UpdateClientPayload) => Promise<void>;
}) {
  const [firstName, setFirstName] = useState(initial?.firstName ?? "");
  const [lastName, setLastName] = useState(initial?.lastName ?? "");
  const [phone, setPhone] = useState(initial?.phone ?? "");
  const [email, setEmail] = useState(initial?.email ?? "");
  const [address, setAddress] = useState(initial?.address ?? "");
  const [postalCode, setPostalCode] = useState(initial?.postalCode ?? "");
  const [city, setCity] = useState(initial?.city ?? "");
  const [landlinePhone, setLandlinePhone] = useState(initial?.landlinePhone ?? "");

  const [loading, setLoading] = useState(false);
  const [formError, setFormError] = useState<string | null>(null);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    setFormError(null);

    if (mode === "create") {
      const payload: CreateClientPayload = {
        firstName, lastName, phone,
        email: email || null,
        address: address || null,
        postalCode: postalCode || null,
        city: city || null,
        landlinePhone: landlinePhone || null,
      };

      const errs = validateCreateClient(payload);
      setFieldErrors(errs);
      if (Object.keys(errs).length) return;

      setLoading(true);
      try {
        await onSubmit(payload);
      } catch {
        setFormError("Création impossible (droits / validation API).");
      } finally {
        setLoading(false);
      }
      return;
    }

    const payload: UpdateClientPayload = {
      firstName, lastName, phone,
      email: email || null,
      address: address || null,
      postalCode: postalCode || null,
      city: city || null,
      landlinePhone: landlinePhone || null,
    };

    const errs = validateUpdateClient(payload);
    setFieldErrors(errs);
    if (Object.keys(errs).length) return;

    setLoading(true);
    try {
      await onSubmit(payload);
    } catch {
      setFormError("Modification impossible (droits / validation API).");
    } finally {
      setLoading(false);
    }
  }

  return (
    <form onSubmit={submit} className="card" style={{ padding: 16, display: "grid", gap: 12, maxWidth: 720 }}>
      <div style={{ display: "grid", gap: 12, gridTemplateColumns: "1fr 1fr" }}>
        <div>
          <label className="small">Prénom</label>
          <input className="input" value={firstName} onChange={(e) => setFirstName(e.target.value)} />
          {fieldErrors.firstName && <div style={{ color: "var(--danger)", fontSize: 13 }}>{fieldErrors.firstName}</div>}
        </div>
        <div>
          <label className="small">Nom</label>
          <input className="input" value={lastName} onChange={(e) => setLastName(e.target.value)} />
          {fieldErrors.lastName && <div style={{ color: "var(--danger)", fontSize: 13 }}>{fieldErrors.lastName}</div>}
        </div>
      </div>

      <div style={{ display: "grid", gap: 12, gridTemplateColumns: "1fr 1fr" }}>
        <div>
          <label className="small">Téléphone</label>
          <input className="input" value={phone} onChange={(e) => setPhone(e.target.value)} />
          {fieldErrors.phone && <div style={{ color: "var(--danger)", fontSize: 13 }}>{fieldErrors.phone}</div>}
        </div>
        <div>
          <label className="small">Email</label>
          <input className="input" value={email ?? ""} onChange={(e) => setEmail(e.target.value)} />
          {fieldErrors.email && <div style={{ color: "var(--danger)", fontSize: 13 }}>{fieldErrors.email}</div>}
        </div>
      </div>

      <div>
        <label className="small">Adresse</label>
        <input className="input" value={address ?? ""} onChange={(e) => setAddress(e.target.value)} />
      </div>

      <div style={{ display: "grid", gap: 12, gridTemplateColumns: "1fr 1fr" }}>
        <div>
          <label className="small">Code postal</label>
          <input className="input" value={postalCode ?? ""} onChange={(e) => setPostalCode(e.target.value)} />
        </div>
        <div>
          <label className="small">Ville</label>
          <input className="input" value={city ?? ""} onChange={(e) => setCity(e.target.value)} />
        </div>
      </div>

      <div>
        <label className="small">Téléphone fixe</label>
        <input className="input" value={landlinePhone ?? ""} onChange={(e) => setLandlinePhone(e.target.value)} />
      </div>

      {formError && <div style={{ color: "var(--danger)", fontSize: 13 }}>{formError}</div>}

      <button className="btn btn-primary" disabled={loading}>
        {loading ? "Enregistrement..." : mode === "create" ? "Créer" : "Mettre à jour"}
      </button>
    </form>
  );
}