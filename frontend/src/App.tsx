import { Routes, Route, Navigate } from "react-router-dom";
import AuthLayout from "./layouts/AuthLayout";
import AdminLayout from "./layouts/AdminLayout";
import { AuthGuard } from "./auth/auth.guard";

import LoginPage from "./pages/LoginPage";
import ForgotPasswordPage from "./pages/ForgotPasswordPage";
import ResetPasswordPage from "./pages/ResetPasswordPage";
import DashboardPage from "./pages/DashboardPage";
import ProfilePage from "./pages/ProfilePage";
import { GlobalLoadingOverlay } from "./ui/GlobalLoadingOverlay";
import UsersListPage from "./features/users/pages/UsersListPage";
import UserCreatePage from "./features/users/pages/UserCreatePage";
import UserShowPage from "./features/users/pages/UserShowPage";
import UserEditPage from "./features/users/pages/UserEditPage";

export default function App() {
  return (
    <>
      <GlobalLoadingOverlay />
      <Routes>
        {/* Public/auth pages */}
        <Route element={<AuthLayout />}>
          <Route path="/login" element={<LoginPage />} />
          <Route path="/forgot-password" element={<ForgotPasswordPage />} />
          <Route path="/reset-password" element={<ResetPasswordPage />} />
        </Route>

        {/* Protected admin pages */}
        <Route
          path="/admin"
          element={
            <AuthGuard>
              <AdminLayout />
            </AuthGuard>
          }
        >
          <Route index element={<DashboardPage />} />
          <Route path="profile" element={<ProfilePage />} />
          <Route path="users" element={<UsersListPage />} />
          <Route path="users/new" element={<UserCreatePage />} />
          <Route path="users/:id" element={<UserShowPage />} />
          <Route path="users/:id/edit" element={<UserEditPage />} />
        </Route>

        {/* Default */}
        <Route path="/" element={<Navigate to="/admin" replace />} />
        <Route path="*" element={<Navigate to="/admin" replace />} />
      </Routes>
    </>
  );
}
