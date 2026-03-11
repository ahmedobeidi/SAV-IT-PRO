import { useMemo, useState } from "react";
import { Link } from "react-router-dom";
import ClientsTable from "../components/ClientsTable";
import { useClientsList } from "../hooks/useClientsList";
import { clientsApi } from "../clients.api";
import type { ClientRead } from "../clients.types";
import { UserPlus } from "lucide-react";
import ConfirmDialog from "../../../components/ConfirmDialog/ConfirmDialog"; // ✅ adjust path if needed

export default function ClientsListPage() {
  const [phone, setPhone] = useState("");
  const [page, setPage] = useState(1);
  const limit = 10;

  const { data, loading, error } = useClientsList(phone, page, limit);

  const totalPages = useMemo(() => {
    if (!data) return 1;
    return Math.max(1, Math.ceil(data.total / data.limit));
  }, [data]);

  // ✅ anonymize confirm dialog state
  const [anonymizing, setAnonymizing] = useState<ClientRead | null>(null);

  async function anonymize() {
    if (!anonymizing) return;
    await clientsApi.anonymize(anonymizing.id);
    setAnonymizing(null);
    window.location.reload(); // keep for now (later: refetch/refresh)
  }

  return (
    <div style={{ display: "grid", gap: 12 }}>
      {/* HEADER */}
      <div
        style={{
          display: "flex",
          justifyContent: "space-between",
          alignItems: "end",
          gap: 12,
        }}
      >
        <div>
          <h2 style={{ margin: 0 }}>Clients</h2>
        </div>

        <Link
          to="/admin/clients/new"
          className="btn btn-primary"
          title="Créer un client"
          aria-label="Créer un client"
          style={{ display: "flex", alignItems: "center", gap: 6 }}
        >
          <UserPlus size={18} />
          Client
        </Link>
      </div>

      {/* CARD */}
      <div
        className="card"
        style={{
          padding: 12,
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
          flexWrap: "wrap",
          gap: 10,
        }}
      >
        {/* LEFT */}
        <div style={{ display: "flex", alignItems: "center", gap: 10, flexWrap: "wrap" }}>
          <input
            className="input"
            style={{ width: 260, flexShrink: 0 }}
            placeholder="Rechercher par téléphone..."
            value={phone}
            onChange={(e) => {
              setPhone(e.target.value);
              setPage(1);
            }}
          />

          <div className="small">
            Page {page} / {totalPages}
          </div>
        </div>

        {/* RIGHT */}
        <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
          <button className="btn" onClick={() => setPage((p) => Math.max(1, p - 1))} disabled={page <= 1}>
            Précédent
          </button>

          <button className="btn" onClick={() => setPage((p) => Math.min(totalPages, p + 1))} disabled={page >= totalPages}>
            Suivant
          </button>
        </div>
      </div>

      {loading && <div className="small">Chargement...</div>}
      {error && <div style={{ color: "var(--danger)", fontSize: 13 }}>{error}</div>}

      {data && (
        <ClientsTable
          items={data.items}
          onAnonymize={(c) => setAnonymizing(c)} // ✅ instead of confirm()
        />
      )}

      <ConfirmDialog
        open={!!anonymizing}
        title="Anonymiser le client"
        message="Confirmer l’anonymisation RGPD ? (action irréversible)"
        danger
        confirmText="Anonymiser"
        cancelText="Annuler"
        onCancel={() => setAnonymizing(null)}
        onConfirm={anonymize}
      />
    </div>
  );
}