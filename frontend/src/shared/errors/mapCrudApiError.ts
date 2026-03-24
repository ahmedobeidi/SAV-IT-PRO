type MapCrudApiErrorOptions = {
  notFoundMessage?: string;
  conflictMessage?: string;
  validationMessage?: string;
  defaultMessage?: string;
};

export function mapCrudApiError(
  error: any,
  options: MapCrudApiErrorOptions = {},
): string {
  const status = error?.response?.status;

  if (status === 401) return "Session expirée. Reconnecte-toi.";
  if (status === 403) return "Accès interdit (droits insuffisants).";
  if (status === 404) return options.notFoundMessage ?? "Ressource introuvable.";
  if (status === 409) {
    return error?.response?.data?.message ?? options.conflictMessage ?? "Conflit.";
  }
  if (status === 422) {
    return options.validationMessage ?? "Validation échouée.";
  }

  return options.defaultMessage ?? "Erreur serveur.";
}
