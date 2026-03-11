import { Navigate } from "react-router-dom";
import { authStore } from "./auth.store";

export function GuestGuard({ children }: { children: React.ReactNode }) {
  if (authStore.isLoggedIn()) {
    return <Navigate to="/admin" replace />;
  }

  return <>{children}</>;
}