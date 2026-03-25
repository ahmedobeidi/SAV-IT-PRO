import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { MemoryRouter } from "react-router-dom";
import { vi, describe, it, expect, beforeEach } from "vitest";
import LoginPage from "../features/auth/pages/LoginPage";

const mockNavigate = vi.fn();
const mockLogin = vi.fn();
const mockSetTokens = vi.fn();

vi.mock("react-router-dom", async () => {
  const actual = await vi.importActual<typeof import("react-router-dom")>(
    "react-router-dom"
  );

  return {
    ...actual,
    useNavigate: () => mockNavigate,
    useLocation: () => ({ pathname: "/login", state: null }),
  };
});

vi.mock("../features/auth/auth.service", () => ({
  authService: {
    login: (...args: unknown[]) => mockLogin(...args),
  },
}));

vi.mock("../features/auth/auth.store", () => ({
  authStore: {
    setTokens: (...args: unknown[]) => mockSetTokens(...args),
  },
}));

describe("LoginPage", () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("submits login form successfully", async () => {
    mockLogin.mockResolvedValue({
      token: "token123",
      refresh_token: "refresh123",
      role: "ROLE_ADMIN",
    });

    const user = userEvent.setup();

    render(
      <MemoryRouter>
        <LoginPage />
      </MemoryRouter>
    );

    await user.type(screen.getByLabelText(/email/i), "admin@test.com");
    await user.type(screen.getByLabelText(/mot de passe/i), "Password123!");
    await user.click(screen.getByRole("button", { name: /se connecter/i }));

    await waitFor(() => {
      expect(mockLogin).toHaveBeenCalledWith("admin@test.com", "Password123!");
    });

    expect(mockSetTokens).toHaveBeenCalledWith(
      "token123",
      "refresh123",
      "ROLE_ADMIN"
    );

    expect(mockNavigate).toHaveBeenCalledWith("/admin", { replace: true });
  });

  it("shows error on failed login", async () => {
    mockLogin.mockRejectedValue({
      response: {
        data: {
          message: "Email ou mot de passe invalide.",
        },
      },
    });

    const user = userEvent.setup();

    render(
      <MemoryRouter>
        <LoginPage />
      </MemoryRouter>
    );

    await user.type(screen.getByLabelText(/email/i), "wrong@test.com");
    await user.type(screen.getByLabelText(/mot de passe/i), "wrongpass");
    await user.click(screen.getByRole("button", { name: /se connecter/i }));

    expect(
      await screen.findByText("Email ou mot de passe invalide.")
    ).toBeInTheDocument();
  });
});