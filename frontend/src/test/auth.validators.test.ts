import {
  validEmail,
  strongPassword,
  EMAIL_ERROR,
  PASSWORD_ERROR,
} from "../features/auth/auth.validators";

describe("auth.validators", () => {
  it("validEmail returns true for valid email", () => {
    expect(validEmail("test@example.com")).toBe(true);
  });

  it("validEmail returns false for invalid email", () => {
    expect(validEmail("bad-email")).toBe(false);
  });

  it("strongPassword returns true for strong password", () => {
    expect(strongPassword("Abcd1234!")).toBe(true);
  });

  it("strongPassword returns false for weak password", () => {
    expect(strongPassword("abc")).toBe(false);
  });

  it("exports error messages", () => {
    expect(EMAIL_ERROR).toContain("adresse e-mail valide");
    expect(PASSWORD_ERROR).toContain("8 caractères");
  });
});