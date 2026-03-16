import { useEffect, useState } from "react";
import { repairsApi } from "../repairs.api";
import { mapApiError } from "../repairs.validators";
import type { TicketRead } from "../repairs.types";

export default function TicketActions({
  repairId,
  onDone,
}: {
  repairId: number;
  onDone: () => void;
}) {
  const [loadingGen, setLoadingGen] = useState(false);
  const [loadingSend, setLoadingSend] = useState(false);
  const [msg, setMsg] = useState<string | null>(null);
  const [tickets, setTickets] = useState<TicketRead[]>([]);
  const [loadingTickets, setLoadingTickets] = useState(false);
  const [busyTicketId, setBusyTicketId] = useState<number | null>(null);

  async function loadTickets() {
    setLoadingTickets(true);
    try {
      const data = await repairsApi.listTickets(repairId);
      setTickets(data);
    } catch {
      setTickets([]);
    } finally {
      setLoadingTickets(false);
    }
  }

  useEffect(() => {
    loadTickets();
  }, [repairId]);

  async function generate() {
    setMsg(null);
    setLoadingGen(true);

    try {
      const t = await repairsApi.generateTicket(repairId);
      setMsg(`Ticket généré : ${t.filename}`);
      await loadTickets();
      onDone();
    } catch (e: any) {
      setMsg(mapApiError(e));
    } finally {
      setLoadingGen(false);
    }
  }

  async function send() {
    setMsg(null);
    setLoadingSend(true);

    try {
      const res = await repairsApi.sendTicket(repairId);
      setMsg(res.message || "Ticket envoyé.");
      await loadTickets();
      onDone();
    } catch (e: any) {
      setMsg(mapApiError(e));
    } finally {
      setLoadingSend(false);
    }
  }

  async function openTicket(ticket: TicketRead) {
    setMsg(null);
    setBusyTicketId(ticket.id);

    try {
      const blob = await repairsApi.viewTicketBlob(ticket.id);
      const url = window.URL.createObjectURL(blob);
      window.open(url, "_blank", "noopener,noreferrer");

      // free memory later
      setTimeout(() => window.URL.revokeObjectURL(url), 60_000);
    } catch (e: any) {
      setMsg(mapApiError(e));
    } finally {
      setBusyTicketId(null);
    }
  }

  async function downloadTicket(ticket: TicketRead) {
    setMsg(null);
    setBusyTicketId(ticket.id);

    try {
      const blob = await repairsApi.downloadTicketBlob(ticket.id);
      const url = window.URL.createObjectURL(blob);

      const a = document.createElement("a");
      a.href = url;
      a.download = ticket.filename;
      document.body.appendChild(a);
      a.click();
      a.remove();

      setTimeout(() => window.URL.revokeObjectURL(url), 60_000);
    } catch (e: any) {
      setMsg(mapApiError(e));
    } finally {
      setBusyTicketId(null);
    }
  }

  return (
    <div style={{ display: "grid", gap: 8 }}>
      <div style={{ display: "flex", gap: 8, flexWrap: "wrap" }}>
        <button className="btn" onClick={generate} disabled={loadingGen}>
          {loadingGen ? "..." : "Générer PDF"}
        </button>

        <button className="btn btn-primary" onClick={send} disabled={loadingSend}>
          {loadingSend ? "..." : "Envoyer au client"}
        </button>
      </div>

      {msg && <div className="small">{msg}</div>}

      <div style={{ display: "grid", gap: 6 }}>
        <div className="small" style={{ fontWeight: 700 }}>
          Tickets PDF
        </div>

        {loadingTickets ? (
          <div className="small">Chargement des tickets...</div>
        ) : tickets.length === 0 ? (
          <div className="small">Aucun ticket généré.</div>
        ) : (
          tickets.map((t) => (
            <div
              key={t.id}
              style={{
                display: "flex",
                justifyContent: "space-between",
                alignItems: "center",
                gap: 8,
                padding: 8,
                border: "1px solid var(--border)",
                borderRadius: 8,
              }}
            >
              <div style={{ minWidth: 0 }}>
                <div style={{ fontWeight: 600 }}>{t.filename}</div>
                <div className="small">
                  v{t.version} • {new Date(t.generatedAt).toLocaleString("fr-FR")} •{" "}
                  {t.isSent ? "Envoyé" : "Non envoyé"}
                </div>
              </div>

              <div style={{ display: "flex", gap: 8, flexWrap: "wrap" }}>
                <button
                  className="btn"
                  onClick={() => openTicket(t)}
                  disabled={busyTicketId === t.id}
                >
                  {busyTicketId === t.id ? "..." : "Ouvrir"}
                </button>

                <button
                  className="btn"
                  onClick={() => downloadTicket(t)}
                  disabled={busyTicketId === t.id}
                >
                  {busyTicketId === t.id ? "..." : "Télécharger"}
                </button>
              </div>
            </div>
          ))
        )}
      </div>
    </div>
  );
}