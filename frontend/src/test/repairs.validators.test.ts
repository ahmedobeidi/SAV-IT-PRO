import {
  validateCreateRepair,
  validateUpdateRepair,
  mapApiError,
} from "../features/repairs/repairs.validators";

describe("repairs.validators", () => {
  it("validateCreateRepair requires client, model and issue", () => {
    const result = validateCreateRepair({
      clientId: 0,
      equipmentModelId: 0,
      issueId: 0,
      price: 10,
      deposit: null,
      description: null,
    });

    expect(result.clientId).toBe("Client obligatoire.");
    expect(result.equipmentModelId).toBe("Modèle obligatoire.");
    expect(result.issueId).toBe("Panne obligatoire.");
  });

  it("validateCreateRepair rejects negative price", () => {
    const result = validateCreateRepair({
      clientId: 1,
      equipmentModelId: 1,
      issueId: 1,
      price: -1,
      deposit: null,
      description: null,
    });

    expect(result.price).toBe("Prix doit être ≥ 0.");
  });

  it("validateUpdateRepair rejects negative deposit", () => {
    const result = validateUpdateRepair({
      equipmentModelId: 1,
      issueId: 1,
      price: 10,
      deposit: -5,
      description: null,
    });

    expect(result.deposit).toBe("Acompte doit être ≥ 0.");
  });

  it("mapApiError maps known statuses", () => {
    expect(mapApiError({ response: { status: 401 } })).toBe("Session expirée.");
    expect(mapApiError({ response: { status: 403 } })).toBe("Accès interdit.");
    expect(mapApiError({ response: { status: 404 } })).toBe("Ressource introuvable.");
    expect(mapApiError({ response: { status: 500 } })).toBe("Erreur serveur.");
  });
});