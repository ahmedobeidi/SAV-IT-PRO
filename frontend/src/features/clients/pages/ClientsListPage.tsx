import { useMemo, useState } from "react";
import { Link } from "react-router-dom";
import ClientsTable from "../components/ClientsTable";
import { useClientsList } from "../hooks/useClientsList";
import { clientsApi } from "../clients.api";
import type { ClientRead } from "../clients.types";
import { UserPlus } from "lucide-react";
import ConfirmDialog from "../../../components/ConfirmDialog/ConfirmDialog";

type BottomPaginationProps = {
  page: number;
  totalPages: number;
  onChange: (page: number) => void;
};

function buildPageItems(page: number, totalPages: number): (number | "...")[] {
  if (totalPages <= 7) {
    return Array.from({ length: totalPages }, (_, i) => i + 1);
  }

  const items: (number | "...")[] = [1];

  const start = Math.max(2, page - 1);
  const end = Math.min(totalPages - 1, page + 1);

  if (start > 2) {
    items.push("...");
  }

  for (let p = start; p <= end; p++) {
    items.push(p);
  }

  if (end < totalPages - 1) {
    items.push("...");
  }

  items.push(totalPages);

  return items;
}

function BottomPagination({
  page,
  totalPages,
  onChange,
}: BottomPaginationProps) {
  if (totalPages <= 1) return null;

  const items = buildPageItems(page, totalPages);

  return (
    <div
      style={{
        display: "flex",
        justifyContent: "center",
        alignItems: "center",
        gap: 8,
        flexWrap: "wrap",
        paddingTop: 6,
      }}
    >
      {items.map((item, i) =>
        item === "..." ? (
          <span key={`dots-${i}`} className="small">
            …
          </span>
        ) : (
          <button
            key={item}
            className="btn"
            onClick={() => onChange(item)}
            aria-current={item === page ? "page" : undefined}
            style={{
              minWidth: 38,
              fontWeight: item === page ? 700 : 400,
              border:
                item === page
                  ? "1px solid var(--primary)"
                  : "1px solid var(--border)",
              background: item === page ? "var(--primary)" : "transparent",
              color: item === page ? "#fff" : "inherit",
              cursor: "pointer",
            }}
          >
            {item}
          </button>
        )
      )}
    </div>
  );
}

export default function ClientsListPage() {
  const [phone, setPhone] = useState("");
  const [page, setPage] = useState(1);
  const limit = 10;

  const { data, loading, error } = useClientsList(phone, page, limit);

  const totalPages = useMemo(() => {
    if (!data) return 1;
    return Math.max(1, Math.ceil(data.total / data.limit));
  }, [data]);

  const [anonymizing, setAnonymizing] = useState<ClientRead | null>(null);

  async function anonymize() {
    if (!anonymizing) return;
    await clientsApi.anonymize(anonymizing.id);
    setAnonymizing(null);
    window.location.reload();
  }

  return (
    <div style={{ display: "grid", gap: 12 }}>
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
        <div
          style={{ display: "flex", alignItems: "center", gap: 10, flexWrap: "wrap" }}
        >
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

        <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
          <button
            className="btn"
            onClick={() => setPage((p) => Math.max(1, p - 1))}
            disabled={page <= 1}
          >
            Précédent
          </button>

          <button
            className="btn"
            onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
            disabled={page >= totalPages}
          >
            Suivant
          </button>
        </div>
      </div>

      {loading && <div className="small">Chargement...</div>}
      {error && <div style={{ color: "var(--danger)", fontSize: 13 }}>{error}</div>}

      {data && (
        <>
          <ClientsTable
            items={data.items}
            onAnonymize={(c) => setAnonymizing(c)}
          />

          <BottomPagination
            page={page}
            totalPages={totalPages}
            onChange={setPage}
          />
        </>
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