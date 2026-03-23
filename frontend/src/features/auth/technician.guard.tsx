import { Navigate } from "react-router-dom";
import { authStore } from "./auth.store";
import { isTechnician } from "./auth.roles";

export function TechnicianGuard({
  children,
}: {
  children: React.ReactNode;
}) {
  const { role } = authStore.getTokens();

  if (!authStore.isLoggedIn()) {
    return <Navigate to="/login" replace />;
  }

  if (!isTechnician(role)) {
    return <Navigate to="/admin" replace />;
  }

  return <>{children}</>;
}