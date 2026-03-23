import { useState } from "react";
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

  async function openTicket() {
    setLoadingTicket(true);

    try {
      const current = await repairsApi.generateCurrentTicket(repairId);
      setTicket(current);

      const blob = await repairsApi.viewTicketBlob(current.id);
      const url = window.URL.createObjectURL(blob);
      window.open(url, "_blank", "noopener,noreferrer");
      setTimeout(() => window.URL.revokeObjectURL(url), 60_000);

      onDone();
    } catch (e: any) {
      onMessage("error", mapApiError(e));
    } finally {
      setLoadingTicket(false);
    }
  }

  return {
    ticket,
    loadingTicket,
    openTicket,
  };
}