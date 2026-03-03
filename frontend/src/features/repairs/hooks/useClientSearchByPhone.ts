import { useState } from "react";
import { clientsApi } from "../../clients/clients.api";
import type { ClientRead } from "../../clients/clients.types";

export function useClientSearchByPhone() {
  const [loading, setLoading] = useState(false);
  const [client, setClient] = useState<ClientRead | null>(null);
  const [error, setError] = useState<string | null>(null);

  async function search(phone: string) {
    setError(null);
    setClient(null);
    const p = phone.trim();
    if (!p) return;

    setLoading(true);
    try {
      const res = await clientsApi.searchByPhone(p);
      setClient(res);
    } catch {
      setError("Client introuvable (ou accès refusé).");
    } finally {
      setLoading(false);
    }
  }

  function reset() {
    setClient(null);
    setError(null);
  }

  return { loading, client, error, search, reset };
}