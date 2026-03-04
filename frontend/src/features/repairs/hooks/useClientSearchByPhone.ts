// useClientSearchByPhone.ts
import { useEffect, useRef, useState } from "react";
import { clientsApi } from "../../clients/clients.api";
import type { ClientRead } from "../../clients/clients.types";

export function useClientSearchByPhone(phone: string) {
  const [loading, setLoading] = useState(false);
  const [client, setClient] = useState<ClientRead | null>(null);
  const [error, setError] = useState<string | null>(null);

  const reqId = useRef(0);

  useEffect(() => {
    const p = phone.replace(/\s/g, "");

    // reset if empty
    if (!p) {
      setClient(null);
      setError(null);
      setLoading(false);
      return;
    }

    // optional: wait until user typed enough digits
    if (p.length < 2) {
      setClient(null);
      setError(null);
      setLoading(false);
      return;
    }

    const t = window.setTimeout(async () => {
      const current = ++reqId.current;
      setLoading(true);
      setError(null);

      try {
        // IMPORTANT: this must use /api/clients?phone=... (same as list page)
        const found = await clientsApi.searchByPhone(p); // returns ClientRead | null

        // ignore old response if user typed again
        if (current !== reqId.current) return;

        if (!found) {
          setClient(null);
          setError("Client introuvable.");
          return;
        }

        setClient(found);
        setError(null);
      } catch {
        if (current !== reqId.current) return;
        setClient(null);
        setError("Erreur lors de la recherche.");
      } finally {
        if (current === reqId.current) setLoading(false);
      }
    }, 300); // ✅ debounce 300ms (like list page feel)

    return () => window.clearTimeout(t);
  }, [phone]);

  function reset() {
    reqId.current += 1; // cancel in-flight
    setClient(null);
    setError(null);
    setLoading(false);
  }

  return { loading, client, error, reset };
}