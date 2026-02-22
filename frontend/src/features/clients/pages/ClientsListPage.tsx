import { useMemo, useState } from "react";
import { Link } from "react-router-dom";
import ClientsTable from "../components/ClientsTable";
import { useClientsList } from "../hooks/useClientsList";
import { clientsApi } from "../clients.api";
import type { ClientRead } from "../clients.types";

export default function ClientsListPage() {
  const [page, setPage] = useState(1);
  const limit = 20;

  const [phone, setPhone] = useState("");
  const [searchResult, setSearchResult] = useState<ClientRead | null>(null);
  const [searchError, setSearchError] = useState<string | null>(null);
  const [searchLoading, setSearchLoading] = useState(false);

  const { data, loading, error } = useClientsList(page, limit);

  const totalPages = useMemo(() => {
    if (!data) return 1;
    return Math.max(1, Math.ceil(data.total / data.limit));
  }, [data]);

  async function onSearch() {
    setSearchError(null);
    setSearchResult(null);
    if (!phone.trim()) return;

    setSearchLoading(true);
    try {
      const c = await clientsApi.searchByPhone(phone.trim());
      setSearchResult(c);
    } catch {
      setSearchError("Client introuvable (ou accès refusé).");
    } finally {
      setSearchLoading(false);
    }
  }

  async function onAnonymize(c: ClientRead) {
    const ok = confirm("Confirmer l’anonymisation RGPD ? (action irréversible)");
    if (!ok) return;
    await clientsApi.anonymize(c.id);
    window.location.reload();
  }

  const items = searchResult ? [searchResult] : data?.items ?? [];

  return (
    <div style={{ display: "grid", gap: 12 }}>
      <div style={{ display: "flex", justifyContent: "space-between", alignItems: "end", gap: 12 }}>
        <div>
          <h2 style={{ margin: 0 }}>Clients</h2>
          <div className="small">Liste + pagination + recherche par téléphone + anonymisation</div>
        </div>

        <Link to="/admin/clients/new" className="btn btn-primary">
          + Créer un client
        </Link>
      </div>

      <div className="card" style={{ padding: 12, display: "grid", gap: 10 }}>
        <div style={{ display: "flex", gap: 10, alignItems: "center", flexWrap: "wrap" }}>
          <input
            className="input"
            placeholder="Rechercher par téléphone (ex: 0601020304)"
            value={phone}
            onChange={(e) => setPhone(e.target.value)}
            style={{ maxWidth: 360 }}
          />
          <button className="btn" onClick={onSearch} disabled={searchLoading}>
            {searchLoading ? "Recherche..." : "Rechercher"}
          </button>
          <button
            className="btn"
            onClick={() => {
              setPhone("");
              setSearchResult(null);
              setSearchError(null);
            }}
          >
            Réinitialiser
          </button>

          <div className="small" style={{ marginLeft: "auto" }}>
            Page {page} / {totalPages}
          </div>
          <button className="btn" onClick={() => setPage((p) => Math.max(1, p - 1))} disabled={page <= 1 || !!searchResult}>
            Précédent
          </button>
          <button className="btn" onClick={() => setPage((p) => Math.min(totalPages, p + 1))} disabled={page >= totalPages || !!searchResult}>
            Suivant
          </button>
        </div>

        {searchError && <div style={{ color: "var(--danger)", fontSize: 13 }}>{searchError}</div>}
        {searchResult && <div className="small">Résultat exact (search by phone).</div>}
      </div>

      {loading && <div className="small">Chargement...</div>}
      {error && <div style={{ color: "var(--danger)", fontSize: 13 }}>{error}</div>}

      <ClientsTable items={items} onAnonymize={onAnonymize} />
    </div>
  );
}