import { http } from "../api/http";

type LoginResponse = {
  token: string;
  refresh_token: string;
  expires_in: number;
  role: string;
};

export const authService = {
  async login(email: string, password: string): Promise<LoginResponse> {
    const res = await http.post("/api/auth/login", { email, password });
    return res.data;
  },

  async refresh(refresh_token: string): Promise<LoginResponse> {
    const res = await http.post("/api/auth/refresh", { refresh_token });
    return res.data;
  },

  async logout(refresh_token: string): Promise<void> {
    await http.post("/api/auth/logout", { refresh_token });
  },

  async forgotPassword(email: string): Promise<void> {
    await http.post("/api/auth/forgot-password", { email });
  },

  async resetPassword(token: string, newPassword: string): Promise<void> {
    await http.post("/api/auth/reset-password", { token, newPassword });
  },
};
