import { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { strongPassword, PASSWORD_ERROR } from "../auth.validators";
import { authService } from "../auth.service";
import { Eye, EyeOff } from "lucide-react";

export default function ResetPasswordPage() {
  const navigate = useNavigate();

  const [token] = useState(() => {
    const hash = window.location.hash;
    const params = new URLSearchParams(hash.replace(/^#/, ""));
    return params.get("token") ?? "";
  });

  const [loading, setLoading] = useState(false);
  const [newPassword, setNewPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [showPasswords, setShowPasswords] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (token) {
      window.history.replaceState({}, document.title, "/reset-password");
    }
  }, [token]);

  const onSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setError(null);
    setLoading(true);

    if (!token) {
      setError("Lien invalide : jeton manquant.");
      setLoading(false);
      return;
    }

    if (!newPassword || !strongPassword(newPassword)) {
      setError(PASSWORD_ERROR);
      setLoading(false);
      return;
    }

    if (newPassword !== confirmPassword) {
      setError("Les mots de passe ne correspondent pas.");
      setLoading(false);
      return;
    }

    try {
      await authService.resetPassword(token, newPassword);

      navigate("/login", {
        state: {
          success: "Mot de passe mis à jour. Vous pouvez vous connecter.",
        },
      });
    } catch {
      setError("Jeton invalide ou expiré.");
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <h2 style={{ marginTop: 0 }}>Réinitialisation du mot de passe</h2>

      <form onSubmit={onSubmit} style={{ display: "grid", gap: 12 }}>
        <div>
          <label className="small label">Nouveau mot de passe</label>

          <div className="password-wrapper">
            <input
              className="input"
              type={showPasswords ? "text" : "password"}
              value={newPassword}
              onChange={(e) => setNewPassword(e.target.value)}
              autoComplete="new-password"
            />

            {newPassword.length > 0 && (
              <button
                type="button"
                className="eye-btn"
                onClick={() => setShowPasswords((prev) => !prev)}
              >
                {showPasswords ? <EyeOff size={18} /> : <Eye size={18} />}
              </button>
            )}
          </div>
        </div>

        <div>
          <label className="small label">
            Confirmer le nouveau mot de passe
          </label>

          <div className="password-wrapper">
            <input
              className="input"
              type={showPasswords ? "text" : "password"}
              value={confirmPassword}
              onChange={(e) => setConfirmPassword(e.target.value)}
              autoComplete="new-password"
            />

            {confirmPassword.length > 0 && (
              <button
                type="button"
                className="eye-btn"
                onClick={() => setShowPasswords((prev) => !prev)}
              >
                {showPasswords ? <EyeOff size={18} /> : <Eye size={18} />}
              </button>
            )}
          </div>
        </div>

        {!token && (
          <div style={{ color: "var(--danger)", fontSize: 13 }}>
            Jeton manquant. Ouvrez le lien reçu par e-mail.
          </div>
        )}

        {error && (
          <div style={{ color: "var(--danger)", fontSize: 13 }}>
            {error}
          </div>
        )}

        <button className="btn btn-primary">
          {loading ? "Mise à jour..." : "Mettre à jour"}
        </button>
      </form>

      <div style={{ marginTop: 12 }} className="small">
        <Link to="/login" style={{ color: "var(--primary)" }}>
          Retour à la connexion
        </Link>
      </div>
    </>
  );
}