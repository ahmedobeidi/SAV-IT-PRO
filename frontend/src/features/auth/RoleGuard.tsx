import { Navigate } from "react-router-dom";
import { authStore } from "./auth.store";
import { getDefaultAdminPath } from "./auth.roles";

type RoleGuardProps = {
  allow: boolean;
  children: React.ReactNode;
};

export function RoleGuard({ allow, children }: RoleGuardProps) {
  const { role } = authStore.getTokens();

  if (!authStore.isLoggedIn()) {
    return <Navigate to="/login" replace />;
  }

  if (!allow) {
    return <Navigate to={getDefaultAdminPath(role)} replace />;
  }

  return <>{children}</>;
}