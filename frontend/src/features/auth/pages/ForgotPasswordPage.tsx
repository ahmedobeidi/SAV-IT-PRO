import { useState } from "react";
import { Link } from "react-router-dom";
import { authService } from "../auth.service";
import { validEmail, EMAIL_ERROR } from "../auth.validators";
import axios from "axios";

export default function ForgotPasswordPage() {
  const [email, setEmail] = useState("");
  const [done, setDone] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const onSubmit = async (e: React.SyntheticEvent<HTMLFormElement>) => {
    e.preventDefault();
    setLoading(true);
    setError(null);
    setDone(false);

    const trimmedEmail = email.trim();

    // ✅ 1. required check
    if (!trimmedEmail) {
      setError("L’e-mail est requis.");
      setLoading(false);
      return;
    }

    // ✅ 2. format validation
    if (!validEmail(trimmedEmail)) {
      setError(EMAIL_ERROR);
      setLoading(false);
      return;
    }

    try {
      await authService.forgotPassword(email);
      setDone(true);
    } catch (err) {
      if (axios.isAxiosError(err)) {
        setError(err.response?.data?.error || err.response?.data?.message || "Erreur serveur.");
        console.log("Forgot password backend error:", err.response?.data);
      } else {
        setError("Erreur serveur.");
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <h2 style={{ marginTop: 0 }}>Mot de passe oublié</h2>

      <form onSubmit={onSubmit} style={{ display: "grid", gap: 12 }}>
        <div>
          <label className="small label">Email</label>
          <input
            className="input"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            autoComplete="email"
          />
        </div>

        {done && (
          <div style={{ marginTop: 0, color: "var(--success)", fontSize: 13 }}>
            Si l’adresse existe, un email de réinitialisation a été envoyé.
          </div>
        )}

        {error && (
          <div style={{ marginTop: 0, color: "var(--danger)", fontSize: 13 }}>
            {error}
          </div>
        )}

        <button className="btn btn-primary" disabled={loading}>
          {loading ? "Envoi..." : "Envoyer le lien de réinitialisation"}
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
