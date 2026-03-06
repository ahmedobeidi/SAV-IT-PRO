import { useEffect, useState } from "react";
import { Link, useSearchParams, useNavigate } from "react-router-dom";
import { strongPassword, PASSWORD_ERROR } from "../auth/auth.validators";
import { authService } from "../auth/auth.service";
import { Eye, EyeOff } from "lucide-react";

export default function ResetPasswordPage() {
  const [searchParams] = useSearchParams();
  const [token, setToken] = useState("");
  const [loading, setLoading] = useState(false);
  const [newPassword, setNewPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const navigate = useNavigate();

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

    if (!newPassword || !strongPassword(newPassword)) {
      setError(PASSWORD_ERROR);
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
              onChange={(e) => {
                setNewPassword(e.target.value);

                if (e.target.value.length === 0) {
                  setShowPassword(false);
                }
              }}
              autoComplete="new-password"
            />

            {newPassword.length > 0 && (
              <button
                type="button"
                className="eye-btn"
                onClick={() => setShowPassword(!showPassword)}
              >
                {showPassword ? <EyeOff size={18} /> : <Eye size={18} />}
              </button>
            )}
          </div>
        </div>

        {error && (
          <div style={{ color: "var(--danger)", fontSize: 13 }}>{error}</div>
        )}

        <button className="btn btn-primary" disabled={!token || loading}>
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
