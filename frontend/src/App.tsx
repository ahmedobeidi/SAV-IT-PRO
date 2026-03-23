import { Routes, Route, Navigate } from "react-router-dom";
import AuthLayout from "./layouts/AuthLayout";
import AdminLayout from "./layouts/AdminLayout";
import { AuthGuard } from "./features/auth/auth.guard";
import { GuestGuard } from "./features/auth/guest.guard";
import { RoleGuard } from "./features/auth/RoleGuard";
import { authStore } from "./features/auth/auth.store";
import {
  canManageUsers,
  canAccessClients,
  canAccessEquipment,
  canAccessRepairs,
  canAccessTechnicianSpace,
  getDefaultAdminPath,
} from "./features/auth/auth.roles";

import LoginPage from "./features/auth/pages/LoginPage";
import ForgotPasswordPage from "./features/auth/pages/ForgotPasswordPage";
import ResetPasswordPage from "./features/auth/pages/ResetPasswordPage";

import DashboardPage from "./features/dashboard/DashboardPage";
import { GlobalLoadingOverlay } from "./components/Loading/GlobalLoadingOverlay";

import UsersListPage from "./features/users/pages/UsersListPage";
import UserCreatePage from "./features/users/pages/UserCreatePage";
import UserEditPage from "./features/users/pages/UserEditPage";

import MyProfilePage from "./features/profile/pages/MyProfilePage";

import ClientsListPage from "./features/clients/pages/ClientsListPage";
import ClientCreatePage from "./features/clients/pages/ClientCreatePage";
import ClientEditPage from "./features/clients/pages/ClientEditPage";

import EquipmentTypesPage from "./features/equipment/pages/EquipmentTypesPage";
import EquipmentBrandsPage from "./features/equipment/pages/EquipmentBrandsPage";
import EquipmentModelsPage from "./features/equipment/pages/EquipmentModelsPage";

import RepairOrdersListPage from "./features/repairs/pages/RepairOrdersListPage";
import RepairOrderCreatePage from "./features/repairs/pages/RepairOrderCreatePage";
import TechnicianRepairOrdersPage from "./features/repairs/pages/TechnicianRepairOrdersPage";

export default function App() {
  const { role } = authStore.getTokens();

  return (
    <>
      <GlobalLoadingOverlay />

      <Routes>
        <Route
          element={
            <GuestGuard>
              <AuthLayout />
            </GuestGuard>
          }
        >
          <Route path="/login" element={<LoginPage />} />
          <Route path="/forgot-password" element={<ForgotPasswordPage />} />
          <Route path="/reset-password" element={<ResetPasswordPage />} />
        </Route>

        <Route
          path="/admin"
          element={
            <AuthGuard>
              <AdminLayout />
            </AuthGuard>
          }
        >
          <Route index element={<DashboardPage />} />
          <Route path="profile" element={<MyProfilePage />} />

          <Route
            path="users"
            element={
              <RoleGuard allow={canManageUsers(role)}>
                <UsersListPage />
              </RoleGuard>
            }
          />
          <Route
            path="users/new"
            element={
              <RoleGuard allow={canManageUsers(role)}>
                <UserCreatePage />
              </RoleGuard>
            }
          />
          <Route
            path="users/:id/edit"
            element={
              <RoleGuard allow={canManageUsers(role)}>
                <UserEditPage />
              </RoleGuard>
            }
          />

          <Route
            path="clients"
            element={
              <RoleGuard allow={canAccessClients(role)}>
                <ClientsListPage />
              </RoleGuard>
            }
          />
          <Route
            path="clients/new"
            element={
              <RoleGuard allow={canAccessClients(role)}>
                <ClientCreatePage />
              </RoleGuard>
            }
          />
          <Route
            path="clients/:id/edit"
            element={
              <RoleGuard allow={canAccessClients(role)}>
                <ClientEditPage />
              </RoleGuard>
            }
          />

          <Route
            path="equipment/types"
            element={
              <RoleGuard allow={canAccessEquipment(role)}>
                <EquipmentTypesPage />
              </RoleGuard>
            }
          />
          <Route
            path="equipment/types/:typeId/brands"
            element={
              <RoleGuard allow={canAccessEquipment(role)}>
                <EquipmentBrandsPage />
              </RoleGuard>
            }
          />
          <Route
            path="equipment/types/:typeId/brands/:brandId/models"
            element={
              <RoleGuard allow={canAccessEquipment(role)}>
                <EquipmentModelsPage />
              </RoleGuard>
            }
          />

          <Route
            path="repair-orders"
            element={
              <RoleGuard allow={canAccessRepairs(role)}>
                <RepairOrdersListPage />
              </RoleGuard>
            }
          />
          <Route
            path="repair-orders/new"
            element={
              <RoleGuard allow={canAccessRepairs(role)}>
                <RepairOrderCreatePage />
              </RoleGuard>
            }
          />

          <Route
            path="technician/repair-orders"
            element={
              <RoleGuard allow={canAccessTechnicianSpace(role)}>
                <TechnicianRepairOrdersPage />
              </RoleGuard>
            }
          />
        </Route>

        <Route path="/" element={<Navigate to={getDefaultAdminPath(role)} replace />} />
        <Route path="*" element={<Navigate to={getDefaultAdminPath(role)} replace />} />
      </Routes>
    </>
  );
}