import { useState } from "react";
import { repairsApi } from "../repairs.api";
import { mapApiError } from "../repairs.validators";

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

  async function generate() {
    setMsg(null);
    setLoadingGen(true);
    try {
      const t = await repairsApi.generateTicket(repairId);
      setMsg(`Ticket généré: ${t.filename} (${t.size} bytes)`);
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
      onDone();
    } catch (e: any) {
      setMsg(mapApiError(e));
    } finally {
      setLoadingSend(false);
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
    </div>
  );
}