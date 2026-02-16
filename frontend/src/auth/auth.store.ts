type Tokens = {
  accessToken: string | null;
  refreshToken: string | null;
  role: string | null;
};

const ACCESS_KEY = "access_token";
const REFRESH_KEY = "refresh_token";
const ROLE_KEY = "user_role";

export const authStore = {
  getTokens(): Tokens {
    return {
      accessToken: localStorage.getItem(ACCESS_KEY),
      refreshToken: localStorage.getItem(REFRESH_KEY),
      role: localStorage.getItem(ROLE_KEY),
    };
  },

  setTokens(accessToken: string, refreshToken: string, role: string) {
    localStorage.setItem(ACCESS_KEY, accessToken);
    localStorage.setItem(REFRESH_KEY, refreshToken);
    localStorage.setItem(ROLE_KEY, role);
  },

  clear() {
    localStorage.removeItem(ACCESS_KEY);
    localStorage.removeItem(REFRESH_KEY);
    localStorage.removeItem(ROLE_KEY);
  },

  isLoggedIn(): boolean {
    const { accessToken } = this.getTokens();
    return !!accessToken;
  },
};
