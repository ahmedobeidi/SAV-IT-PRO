import { useMemo, useState } from "react";
import type { CreateUserPayload, UpdateUserPayload, UserRole, UserRead } from "../users.types";
import { ALL_ROLES, validateCreate, validateUpdate } from "../users.validators";

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
  const [password, setPassword] = useState("");
  const [role, setRole] = useState<UserRole>((initial?.role as UserRole) ?? "ROLE_TECHNICIAN");

  const [loading, setLoading] = useState(false);
  const [formError, setFormError] = useState<string | null>(null);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});

  const roles = useMemo(() => ALL_ROLES, []);

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    setFormError(null);

    if (mode === "create") {
      const payload: CreateUserPayload = { firstName, lastName, email, password, role };
      const errs = validateCreate(payload);
      setFieldErrors(errs);
      if (Object.keys(errs).length) return;

      setLoading(true);
      try {
        await onSubmit(payload);
      } catch (err: any) {
        setFormError("Création impossible (vérifiez les droits / email unique).");
      } finally {
        setLoading(false);
      }
      return;
    }

    // edit
    const payload: UpdateUserPayload = {
      firstName,
      lastName,
      email,
      role,
      ...(password ? { password } : {}),
    };
    const errs = validateUpdate(payload);
    setFieldErrors(errs);
    if (Object.keys(errs).length) return;

    setLoading(true);
    try {
      await onSubmit(payload);
    } catch (err: any) {
      setFormError("Modification impossible (vérifiez les droits / contraintes).");
    } finally {
      setLoading(false);
    }
  }

  return (
    <form onSubmit={submit} className="card" style={{ padding: 16, display: "grid", gap: 12, maxWidth: 720 }}>
      <div>
        <label className="small label">Prénom</label>
        <input className="input" value={firstName} onChange={(e) => setFirstName(e.target.value)} />
        {fieldErrors.firstName && <div style={{ color: "var(--danger)", fontSize: 13 }}>{fieldErrors.firstName}</div>}
      </div>

      <div>
        <label className="small label">Nom</label>
        <input className="input" value={lastName} onChange={(e) => setLastName(e.target.value)} />
        {fieldErrors.lastName && <div style={{ color: "var(--danger)", fontSize: 13 }}>{fieldErrors.lastName}</div>}
      </div>

      <div>
        <label className="small label">Email</label>
        <input className="input" value={email} onChange={(e) => setEmail(e.target.value)} autoComplete="email" />
        {fieldErrors.email && <div style={{ color: "var(--danger)", fontSize: 13 }}>{fieldErrors.email}</div>}
      </div>

      <div>
        <label className="small label">
          Mot de passe {mode === "edit" && <span className="small">(laisser vide pour ne pas changer)</span>}
        </label>
        <input
          className="input"
          type="password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          autoComplete={mode === "create" ? "new-password" : "new-password"}
        />
        {fieldErrors.password && <div style={{ color: "var(--danger)", fontSize: 13 }}>{fieldErrors.password}</div>}
      </div>

      <div>
        <label className="small label">Rôle</label>
        <select className="input" value={role} onChange={(e) => setRole(e.target.value as UserRole)}>
          {roles.map((r) => (
            <option key={r} value={r}>
              {r}
            </option>
          ))}
        </select>
        {fieldErrors.role && <div style={{ color: "var(--danger)", fontSize: 13 }}>{fieldErrors.role}</div>}
      </div>

      {formError && <div style={{ color: "var(--danger)", fontSize: 13 }}>{formError}</div>}

      <button className="btn btn-primary" disabled={loading}>
        {loading ? "Enregistrement..." : mode === "create" ? "Créer" : "Mettre à jour"}
      </button>
    </form>
  );
}
