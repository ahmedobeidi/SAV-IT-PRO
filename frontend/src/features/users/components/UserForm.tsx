import { useState } from "react";
import type {
  CreateUserPayload,
  UpdateUserPayload,
  UserRole,
  UserRead,
} from "../users.types";
import {
  ALL_ROLES,
  ROLE_LABEL,
  validateCreate,
  validateUpdate,
} from "../users.validators";

type Mode = "create" | "edit";

export default function UserForm({
  mode,
  initial,
  onSubmit,
}: {
  mode: Mode;
  initial?: UserRead;
  onSubmit: (payload: CreateUserPayload | UpdateUserPayload) => Promise<void>;
}) {
  const [firstName, setFirstName] = useState(initial?.firstName ?? "");
  const [lastName, setLastName] = useState(initial?.lastName ?? "");
  const [email, setEmail] = useState(initial?.email ?? "");
  const [role, setRole] = useState<UserRole>(
    (initial?.role as UserRole) ?? "ROLE_RECEPTION",
  );

  const [loading, setLoading] = useState(false);
  const [formError, setFormError] = useState<string | null>(null);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});

  function applyBackendErrors(err: any) {
    const data = err?.response?.data;

    setFieldErrors({});
    setFormError(null);

    if (data?.errors && Array.isArray(data.errors)) {
      const fe: Record<string, string> = {};
      for (const e of data.errors) {
        if (e?.field && e?.message && !fe[e.field]) fe[e.field] = e.message;
      }

      setFieldErrors(fe);

      if (Object.keys(fe).length === 0) {
        setFormError(data?.message ?? "Validation échouée.");
      }

      return;
    }

    const s = err?.response?.status;
    if (s === 401) {
      setFormError("Session expirée. Reconnecte-toi.");
    } else if (s === 403) {
      setFormError("Accès interdit (droits insuffisants).");
    } else if (s === 422) {
      setFormError(data?.message ?? "Validation échouée.");
    } else {
      setFormError(data?.message ?? "Erreur serveur.");
    }
  }

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    setFormError(null);

    if (mode === "create") {
      const payload: CreateUserPayload = {
        firstName,
        lastName,
        email,
        role,
      };

      const errs = validateCreate(payload);
      setFieldErrors(errs);
      if (Object.keys(errs).length) return;

      setLoading(true);
      try {
        await onSubmit(payload);
      } catch (err: any) {
        applyBackendErrors(err);
      } finally {
        setLoading(false);
      }
      return;
    }

    const payload: UpdateUserPayload = {
      firstName,
      lastName,
      email,
      role,
    };

    const errs = validateUpdate(payload);
    setFieldErrors(errs);
    if (Object.keys(errs).length) return;

    setLoading(true);
    try {
      await onSubmit(payload);
    } catch (err: any) {
      applyBackendErrors(err);
    } finally {
      setLoading(false);
    }
  }

  return (
    <form
      onSubmit={submit}
      className="card"
      style={{ padding: 16, display: "grid", gap: 12, maxWidth: 720 }}
    >
      <div>
        <label className="small label">Nom</label>
        <input
          className="input"
          value={lastName}
          onChange={(e) => setLastName(e.target.value)}
        />
        {fieldErrors.lastName && (
          <div style={{ color: "var(--danger)", fontSize: 13 }}>
            {fieldErrors.lastName}
          </div>
        )}
      </div>

      <div>
        <label className="small label">Prénom</label>
        <input
          className="input"
          value={firstName}
          onChange={(e) => setFirstName(e.target.value)}
        />
        {fieldErrors.firstName && (
          <div style={{ color: "var(--danger)", fontSize: 13 }}>
            {fieldErrors.firstName}
          </div>
        )}
      </div>

      <div>
        <label className="small label">Email</label>
        <input
          className="input"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          autoComplete="email"
        />
        {fieldErrors.email && (
          <div style={{ color: "var(--danger)", fontSize: 13 }}>
            {fieldErrors.email}
          </div>
        )}
      </div>

      <div>
        <label className="small label">Rôle</label>
        <select
          className="input"
          value={role}
          onChange={(e) => setRole(e.target.value as UserRole)}
        >
          {ALL_ROLES.map((r) => (
            <option key={r} value={r}>
              {ROLE_LABEL[r]}
            </option>
          ))}
        </select>
        {fieldErrors.role && (
          <div style={{ color: "var(--danger)", fontSize: 13 }}>
            {fieldErrors.role}
          </div>
        )}
      </div>

      {mode === "create" && (
        <div className="small" style={{ color: "var(--muted, #666)" }}>
          Un email sera envoyé à l’employé pour définir son mot de passe.
        </div>
      )}

      {formError && (
        <div style={{ color: "var(--danger)", fontSize: 13 }}>{formError}</div>
      )}

      <button className="btn btn-primary" disabled={loading}>
        {loading
          ? "Enregistrement..."
          : mode === "create"
            ? "Créer"
            : "Mettre à jour"}
      </button>
    </form>
  );
}
