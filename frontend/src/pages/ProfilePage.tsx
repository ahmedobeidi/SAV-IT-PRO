import { useEffect, useState } from "react";
import { http } from "../api/http";

export default function ProfilePage() {
  const [data, setData] = useState<any>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    (async () => {
      try {
        // Change to your real protected endpoint
        const res = await http.get("/api/profile");
        setData(res.data);
      } catch (e: any) {
        setError("Impossible de charger le profil (vérifiez que l’endpoint existe).");
      }
    })();
  }, []);

  return (
    <div className="card" style={{ padding: 16 }}>
      <h2 style={{ marginTop: 0 }}>Profil</h2>

      {error && <div style={{ color: "var(--danger)", fontSize: 13 }}>{error}</div>}

      <pre style={{ whiteSpace: "pre-wrap" }}>
        {data ? JSON.stringify(data, null, 2) : "Chargement..."}
      </pre>
    </div>
  );
}
