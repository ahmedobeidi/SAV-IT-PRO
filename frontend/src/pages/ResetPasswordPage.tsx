import { useEffect, useState } from "react";
import { Link, useSearchParams } from "react-router-dom";
import { authService } from "../auth/auth.service";

export default function ResetPasswordPage() {
  const [searchParams] = useSearchParams();
  const [token, setToken] = useState("");
  const [loading, setLoading] = useState(false);
  const [newPassword, setNewPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [done, setDone] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const t = searchParams.get("token") ?? "";
    setToken(t);
    // Optional: remove token from URL after reading (clean + safer)
    window.history.replaceState({}, document.title, "/reset-password");
  }, [searchParams]);

  const onSubmit = async (e: React.SyntheticEvent<HTMLFormElement>) => {
    e.preventDefault();
    setError(null);
    setLoading(true);

    if (!token) {
      setError("Lien invalide: jeton manquant.");
      setLoading(false);
      return;
    }

    try {
      await authService.resetPassword(token, newPassword);
      setDone(true);
    } catch {
      setError("Jeton invalide ou expiré.");
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <h2 style={{ marginTop: 0 }}>Réinitialisation du mot de passe</h2>

      {!token && (
        <div style={{ color: "var(--danger)", fontSize: 13 }}>
          Jeton manquant. Ouvrez le lien reçu par e-mail.
        </div>
      )}

      <form onSubmit={onSubmit} style={{ display: "grid", gap: 12 }}>
        <div>
          <label className="small label">Nouveau mot de passe</label>
          <div className="password-wrapper">
            <input
              className="input"
              type={showPassword ? "text" : "password"}
              value={newPassword}
              onChange={(e) => setNewPassword(e.target.value)}
              autoComplete="current-password"
            />

            <button
              type="button"
              className="eye-btn"
              onClick={() => setShowPassword(!showPassword)}
            >
              {showPassword ? "🙈" : "👁️"}
            </button>
          </div>
        </div>

        {error && (
          <div style={{ color: "var(--danger)", fontSize: 13 }}>{error}</div>
        )}

        <button className="btn btn-primary" disabled={!token || loading}>
          {loading ? "Mise à jour..." : "Mettre à jour"}
        </button>
      </form>

      {done && (
        <div style={{ marginTop: 12, color: "var(--success)", fontSize: 13 }}>
          Mot de passe mis à jour. Vous pouvez vous connecter.
        </div>
      )}

      <div style={{ marginTop: 12 }} className="small">
        <Link to="/login" style={{ color: "var(--primary)" }}>
          Retour à la connexion
        </Link>
      </div>
    </>
  );
}
