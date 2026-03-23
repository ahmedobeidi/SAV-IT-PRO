import { Navigate } from "react-router-dom";
import { useAuth } from "./useAuth";
import { getDefaultAdminPath } from "./auth.roles";

type RoleGuardProps = {
  allow: boolean;
  children: React.ReactNode;
};

export function RoleGuard({ allow, children }: RoleGuardProps) {
  const { isLoggedIn, role } = useAuth();

  if (!isLoggedIn) {
    return <Navigate to="/login" replace />;
  }

  if (!allow) {
    return <Navigate to={getDefaultAdminPath(role)} replace />;
  }

  return <>{children}</>;
}