import {
  validateCreateClient,
  validateUpdateClient,
} from "../features/clients/clients.validators";

describe("clients.validators", () => {
  it("validateCreateClient returns errors for empty required fields", () => {
    const result = validateCreateClient({
      firstName: "",
      lastName: "",
      phone: "",
      email: null,
      address: null,
      postalCode: null,
      city: null,
      landlinePhone: null,
    });

    expect(result.firstName).toBe("Prénom obligatoire.");
    expect(result.lastName).toBe("Nom obligatoire.");
    expect(result.phone).toBe("Téléphone obligatoire.");
  });

  it("validateCreateClient validates invalid email", () => {
    const result = validateCreateClient({
      firstName: "John",
      lastName: "Doe",
      phone: "0600000000",
      email: "bad-email",
      address: null,
      postalCode: null,
      city: null,
      landlinePhone: null,
    });

    expect(result.email).toBe("Email invalide.");
  });

  it("validateUpdateClient allows empty payload", () => {
    const result = validateUpdateClient({});
    expect(result).toEqual({});
  });
});