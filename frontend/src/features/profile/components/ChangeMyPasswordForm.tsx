import { useState } from "react";
import { profileApi } from "../profile.api";
import type { ChangeMyPasswordPayload } from "../profile.types";
import { validateChangeMyPassword } from "../profile.validators";

export default function ChangeMyPasswordForm() {
  const [currentPassword, setCurrentPassword] = useState("");
  const [newPassword, setNewPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");

  const [loading, setLoading] = useState(false);
  const [success, setSuccess] = useState<string | null>(null);
  const [formError, setFormError] = useState<string | null>(null);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});

  function applyBackendErrors(err: any) {
    const data = err?.response?.data;

    setFieldErrors({});
    setFormError(null);
    setSuccess(null);

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
    if (s === 401) setFormError("Session expirée. Reconnecte-toi.");
    else if (s === 403) setFormError("Accès interdit.");
    else setFormError(data?.message ?? "Erreur serveur.");
  }

  async function submit(e: React.FormEvent) {
    e.preventDefault();

    setSuccess(null);
    setFormError(null);
    setFieldErrors({});

    const payload: ChangeMyPasswordPayload = {
      currentPassword,
      newPassword,
      confirmPassword,
    };

    const errs = validateChangeMyPassword(payload);
    setFieldErrors(errs);
    if (Object.keys(errs).length) return;

    setLoading(true);
    try {
      const res = await profileApi.changeMyPassword(payload);
      setSuccess(res.message);
      setCurrentPassword("");
      setNewPassword("");
      setConfirmPassword("");
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
      <h3 style={{ margin: 0 }}>Changer mon mot de passe</h3>

      <div>
        <label className="small label">Mot de passe actuel</label>
        <input
          className="input"
          type="password"
          value={currentPassword}
          onChange={(e) => setCurrentPassword(e.target.value)}
          autoComplete="current-password"
        />
        {fieldErrors.currentPassword && (
          <div style={{ color: "var(--danger)", fontSize: 13 }}>
            {fieldErrors.currentPassword}
          </div>
        )}
      </div>

      <div>
        <label className="small label">Nouveau mot de passe</label>
        <input
          className="input"
          type="password"
          value={newPassword}
          onChange={(e) => setNewPassword(e.target.value)}
          autoComplete="new-password"
        />
        {fieldErrors.newPassword && (
          <div style={{ color: "var(--danger)", fontSize: 13 }}>
            {fieldErrors.newPassword}
          </div>
        )}
      </div>

      <div>
        <label className="small label">Confirmer le nouveau mot de passe</label>
        <input
          className="input"
          type="password"
          value={confirmPassword}
          onChange={(e) => setConfirmPassword(e.target.value)}
          autoComplete="new-password"
        />
        {fieldErrors.confirmPassword && (
          <div style={{ color: "var(--danger)", fontSize: 13 }}>
            {fieldErrors.confirmPassword}
          </div>
        )}
      </div>

      {formError && (
        <div style={{ color: "var(--danger)", fontSize: 13 }}>{formError}</div>
      )}

      {success && (
        <div style={{ color: "var(--success)", fontSize: 13 }}>{success}</div>
      )}

      <button className="btn btn-primary" disabled={loading}>
        {loading ? "Enregistrement..." : "Mettre à jour le mot de passe"}
      </button>
    </form>
  );
}