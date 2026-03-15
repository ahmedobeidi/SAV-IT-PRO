import { useState } from "react";
import { profileApi } from "../profile.api";
import type { ChangeMyPasswordPayload } from "../profile.types";
import { validateChangeMyPassword } from "../profile.validators";
import { Eye, EyeOff } from "lucide-react";

export default function ChangeMyPasswordForm() {
  const [currentPassword, setCurrentPassword] = useState("");
  const [newPassword, setNewPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");

  const [showCurrent, setShowCurrent] = useState(false);
  const [showNew, setShowNew] = useState(false);
  const [showConfirm, setShowConfirm] = useState(false);

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

      setShowCurrent(false);
      setShowNew(false);
      setShowConfirm(false);
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
      style={{ padding: 16, display: "grid", gap: 12 }}
    >
      <h3 style={{ margin: 0 }}>Changer mon mot de passe</h3>

      {/* Current password */}
      <div>
        <label className="small label">Mot de passe actuel</label>
        <div className="password-wrapper">
          <input
            className="input"
            type={showCurrent ? "text" : "password"}
            value={currentPassword}
            onChange={(e) => {
              setCurrentPassword(e.target.value);
              if (e.target.value.length === 0) setShowCurrent(false);
            }}
            autoComplete="current-password"
          />

          {currentPassword.length > 0 && (
            <button
              type="button"
              className="eye-btn"
              onClick={() => setShowCurrent(!showCurrent)}
            >
              {showCurrent ? <EyeOff size={18} /> : <Eye size={18} />}
            </button>
          )}
        </div>

        {fieldErrors.currentPassword && (
          <div style={{ color: "var(--danger)", fontSize: 13 }}>
            {fieldErrors.currentPassword}
          </div>
        )}
      </div>

      {/* New password */}
      <div>
        <label className="small label">Nouveau mot de passe</label>
        <div className="password-wrapper">
          <input
            className="input"
            type={showNew ? "text" : "password"}
            value={newPassword}
            onChange={(e) => {
              setNewPassword(e.target.value);
              if (e.target.value.length === 0) setShowNew(false);
            }}
            autoComplete="new-password"
          />

          {newPassword.length > 0 && (
            <button
              type="button"
              className="eye-btn"
              onClick={() => setShowNew(!showNew)}
            >
              {showNew ? <EyeOff size={18} /> : <Eye size={18} />}
            </button>
          )}
        </div>

        {fieldErrors.newPassword && (
          <div style={{ color: "var(--danger)", fontSize: 13 }}>
            {fieldErrors.newPassword}
          </div>
        )}
      </div>

      {/* Confirm password */}
      <div>
        <label className="small label">Confirmer le nouveau mot de passe</label>
        <div className="password-wrapper">
          <input
            className="input"
            type={showConfirm ? "text" : "password"}
            value={confirmPassword}
            onChange={(e) => {
              setConfirmPassword(e.target.value);
              if (e.target.value.length === 0) setShowConfirm(false);
            }}
            autoComplete="new-password"
          />

          {confirmPassword.length > 0 && (
            <button
              type="button"
              className="eye-btn"
              onClick={() => setShowConfirm(!showConfirm)}
            >
              {showConfirm ? <EyeOff size={18} /> : <Eye size={18} />}
            </button>
          )}
        </div>

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