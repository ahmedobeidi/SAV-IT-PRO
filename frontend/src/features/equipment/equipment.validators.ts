export function validateName(name: string): string | null {
  const v = name.trim();
  if (!v) return "Nom obligatoire.";
  if (v.length > 150) return "Nom trop long (max 150).";
  return null;
}