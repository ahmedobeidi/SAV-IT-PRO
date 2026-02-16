import { useState } from "react";
import { Link } from "react-router-dom";
import { authService } from "../auth/auth.service";

export default function ForgotPasswordPage() {
  const [email, setEmail] = useState("");
  const [done, setDone] = useState(false);
  const [loading, setLoading] = useState(false);

  const onSubmit = async (e: React.SyntheticEvent<HTMLFormElement>) => {
    e.preventDefault();
    setLoading(true);
    try {
      await authService.forgotPassword(email);
      setDone(true);
    } finally {
      setLoading(false);
    }
  }

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

        <button className="btn btn-primary" disabled={loading}>
          {loading ? "Envoi..." : "Envoyer le lien de réinitialisation"}
        </button>
      </form>

      {done && (
        <div style={{ marginTop: 12, color: "var(--success)", fontSize: 13 }}>
          Si l’adresse existe, un email de réinitialisation a été envoyé.
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
