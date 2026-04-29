import { useCallback, useState } from "react";

export type FlashMessage = {
  id: number;
  type: "success" | "error";
  text: string;
};

export function useFlashMessage(timeoutMs = 5000) {
  const [flash, setFlash] = useState<FlashMessage | null>(null);

  const showFlash = useCallback(
    (type: FlashMessage["type"], text: string) => {
      const id = Date.now();
      setFlash({ id, type, text });

      window.setTimeout(() => {
        setFlash((current) => (current?.id === id ? null : current));
      }, timeoutMs);
    },
    [timeoutMs],
  );

  return { flash, setFlash, showFlash };
}
