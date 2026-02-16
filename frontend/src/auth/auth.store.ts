type Tokens = {
  accessToken: string | null;
  refreshToken: string | null;
};

const ACCESS_KEY = "epic1_access_token";
const REFRESH_KEY = "epic1_refresh_token";

export const authStore = {
  getTokens(): Tokens {
    return {
      accessToken: localStorage.getItem(ACCESS_KEY),
      refreshToken: localStorage.getItem(REFRESH_KEY),
    };
  },

  setTokens(accessToken: string, refreshToken: string) {
    localStorage.setItem(ACCESS_KEY, accessToken);
    localStorage.setItem(REFRESH_KEY, refreshToken);
  },

  clear() {
    localStorage.removeItem(ACCESS_KEY);
    localStorage.removeItem(REFRESH_KEY);
  },

  isLoggedIn(): boolean {
    const { accessToken } = this.getTokens();
    return !!accessToken;
  },
};
