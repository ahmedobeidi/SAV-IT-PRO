import { useEffect, useState } from "react";
import { repairsApi } from "../repairs.api";
import { mapApiError } from "../repairs.validators";
import type { TicketRead } from "../repairs.types";

export function useTicketActions(
  repairId: number,
  onDone: () => void,
  onMessage: (type: "success" | "error", text: string) => void
) {
  const [ticket, setTicket] = useState<TicketRead | null>(null);
  const [loadingTicket, setLoadingTicket] = useState(false);

  async function loadTicket() {
    setLoadingTicket(true);
    try {
      const data = await repairsApi.listTickets(repairId);
      setTicket(data.length > 0 ? data[0] : null);
    } catch {
      setTicket(null);
    } finally {
      setLoadingTicket(false);
    }
  }

  useEffect(() => {
    loadTicket();
  }, [repairId]);

  async function send() {
    try {
      const res = await repairsApi.sendCurrentTicket(repairId);
      onMessage("success", res.message || "Ticket envoyé.");
      await loadTicket();
      onDone();
    } catch (e: any) {
      onMessage("error", mapApiError(e));
    }
  }

  async function openTicket() {
    try {
      // Always get/generate the current version first
      const current = await repairsApi.generateCurrentTicket(repairId);
      setTicket(current);

      const blob = await repairsApi.viewTicketBlob(current.id);
      const url = window.URL.createObjectURL(blob);
      window.open(url, "_blank", "noopener,noreferrer");
      setTimeout(() => window.URL.revokeObjectURL(url), 60_000);

      onDone();
    } catch (e: any) {
      onMessage("error", mapApiError(e));
    }
  }

  return {
    ticket,
    loadingTicket,
    send,
    openTicket,
    reload: loadTicket,
  };
}