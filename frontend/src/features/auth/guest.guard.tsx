import { Navigate } from "react-router-dom";
import { useAuth } from "./useAuth";
import { getDefaultAdminPath } from "./auth.roles";

export function GuestGuard({ children }: { children: React.ReactNode }) {
  const { isLoggedIn, role } = useAuth();

  if (isLoggedIn) {
    return <Navigate to={getDefaultAdminPath(role)} replace />;
  }

  return <>{children}</>;
}
