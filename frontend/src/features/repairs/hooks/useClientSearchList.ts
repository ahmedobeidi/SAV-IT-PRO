import { useEffect, useRef, useState } from "react";
import { clientsApi } from "../../clients/clients.api";
import type { ClientRead } from "../../clients/clients.types";

export function useClientSearchList(phone: string) {
  const [loading, setLoading] = useState(false);
  const [items, setItems] = useState<ClientRead[]>([]);
  const [error, setError] = useState<string | null>(null);

  const reqId = useRef(0);

  useEffect(() => {
    const p = phone.replace(/\s/g, "");

    if (!p || p.length < 2) {
      setItems([]);
      setError(null);
      return;
    }

    const timeout = setTimeout(async () => {
      const current = ++reqId.current;

      setLoading(true);
      setError(null);

      try {
        const res = await clientsApi.listSilent({
          phone: p,
          page: 1,
          limit: 10,
        });

        if (current !== reqId.current) return;

        setItems(res.items ?? []);

        if (!res.items?.length) {
          setError("Aucun client trouvé.");
        }
      } catch {
        if (current !== reqId.current) return;
        setError("Erreur lors de la recherche.");
        setItems([]);
      } finally {
        if (current === reqId.current) setLoading(false);
      }
    }, 300);

    return () => clearTimeout(timeout);
  }, [phone]);

  return {
    loading,
    items,
    error,
  };
}