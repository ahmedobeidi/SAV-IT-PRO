type Tokens = {
  accessToken: string | null;
  refreshToken: string | null;
  role: string | null;
};

type Listener = () => void;

const ACCESS_KEY = "access_token";
const REFRESH_KEY = "refresh_token";
const ROLE_KEY = "user_role";

let listeners: Listener[] = [];

function readFromStorage(): Tokens {
  return {
    accessToken: localStorage.getItem(ACCESS_KEY),
    refreshToken: localStorage.getItem(REFRESH_KEY),
    role: localStorage.getItem(ROLE_KEY),
  };
}

let snapshot: Tokens = readFromStorage();

function emitChange() {
  listeners.forEach((listener) => listener());
}

export const authStore = {
  getTokens(): Tokens {
    return snapshot;
  },

  setTokens(accessToken: string, refreshToken: string, role: string) {
    localStorage.setItem(ACCESS_KEY, accessToken);
    localStorage.setItem(REFRESH_KEY, refreshToken);
    localStorage.setItem(ROLE_KEY, role);

    snapshot = {
      accessToken,
      refreshToken,
      role,
    };

    emitChange();
  },

  clear() {
    localStorage.removeItem(ACCESS_KEY);
    localStorage.removeItem(REFRESH_KEY);
    localStorage.removeItem(ROLE_KEY);

    snapshot = {
      accessToken: null,
      refreshToken: null,
      role: null,
    };

    emitChange();
  },

  isLoggedIn(): boolean {
    return !!snapshot.accessToken;
  },

  subscribe(listener: Listener) {
    listeners.push(listener);

    return () => {
      listeners = listeners.filter((l) => l !== listener);
    };
  },
};