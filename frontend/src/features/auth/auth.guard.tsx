import { Navigate } from "react-router-dom";
import { authStore } from "./auth.store";

export function AuthGuard({ children }: { children: React.ReactNode }) {
  if (!authStore.isLoggedIn()) {
    return <Navigate to="/login" replace />;
  }
  return <>{children}</>;
}
