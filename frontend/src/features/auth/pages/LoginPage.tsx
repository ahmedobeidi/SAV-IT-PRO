import { useEffect, useState } from "react";
import { Link, useNavigate, useLocation } from "react-router-dom";
import { authService } from "../auth.service";
import { authStore } from "../auth.store";
import { Eye, EyeOff } from "lucide-react";

export default function LoginPage() {
  const navigate = useNavigate();
  const location = useLocation();

  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [successMessage, setSuccessMessage] = useState<string | null>(null);

  useEffect(() => {
    const message = (location.state as { success?: string } | null)?.success;

    if (message) {
      setSuccessMessage(message);

      // clear router state so it does not persist on refresh/back
      navigate(location.pathname, { replace: true });
    }
  }, [location.state, location.pathname, navigate]);

  // auto-hide after 5 seconds
  useEffect(() => {
    if (!successMessage) return;

    const timer = setTimeout(() => {
      setSuccessMessage(null);
    }, 5000);

    return () => clearTimeout(timer);
  }, [successMessage]);

  const onSubmit = async (e: React.SyntheticEvent<HTMLFormElement>) => {
    e.preventDefault();
    setError(null);
    setLoading(true);

    try {
      const res = await authService.login(email, password);
      authStore.setTokens(res.token, res.refresh_token, res.role);
      navigate("/admin");
    } catch (err: any) {
      setError(
        err?.response?.data?.message ?? "Email ou mot de passe invalide."
      );
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <h2 style={{ marginTop: 0 }}>Connexion</h2>

      <form onSubmit={onSubmit} style={{ display: "grid", gap: 12 }}>
        <div>
          <label className="small label">Email</label>
          <input
            className="input"
            value={email}
            onChange={(e) => { setEmail(e.target.value); setError(null); }}
            autoComplete="email"
          />
        </div>

        <div>
          <label className="small label">Mot de passe</label>
          <div className="password-wrapper">
            <input
              className="input"
              type={showPassword ? "text" : "password"}
              value={password}
              onChange={(e) => {
                setPassword(e.target.value);
                setError(null);

                if (e.target.value.length === 0) {
                  setShowPassword(false);
                }
              }}
              autoComplete="current-password"
            />

            {password.length > 0 && (
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

        {successMessage && (
          <div style={{ color: "var(--success)", fontSize: 13, marginBottom: 0 }}>
            {successMessage}
          </div>
        )}

        {error && (
          <div style={{ color: "var(--danger)", fontSize: 13 }}>{error}</div>
        )}

        <button className="btn btn-primary" disabled={loading}>
          {loading ? "Connexion..." : "Se connecter"}
        </button>
      </form>

      <div style={{ marginTop: 12 }} className="small">
        Mot de passe oublié ?{" "}
        <Link to="/forgot-password" style={{ color: "var(--primary)" }}>
          Réinitialiser
        </Link>
      </div>
    </>
  );
}