export function strongPassword(value: string): boolean {
  return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/.test(value);
}

export const PASSWORD_ERROR =
  "8 caractères min + 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial.";