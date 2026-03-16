import { Routes, Route, Navigate } from "react-router-dom";
import AuthLayout from "./layouts/AuthLayout";
import AdminLayout from "./layouts/AdminLayout";
import { AuthGuard } from "./features/auth/auth.guard";
import { GuestGuard } from "./features/auth/guest.guard";

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
          <Route path="users" element={<UsersListPage />} />
          <Route path="users/new" element={<UserCreatePage />} />
          <Route path="users/:id/edit" element={<UserEditPage />} />
          
          <Route path="clients" element={<ClientsListPage />} />
          <Route path="clients/new" element={<ClientCreatePage />} />
          <Route path="clients/:id/edit" element={<ClientEditPage />} />

          <Route path="equipment/types" element={<EquipmentTypesPage />} />
          <Route path="equipment/types/:typeId/brands" element={<EquipmentBrandsPage />} />
          <Route path="equipment/types/:typeId/brands/:brandId/models" element={<EquipmentModelsPage />} />

          <Route path="repair-orders" element={<RepairOrdersListPage />} />
          <Route path="repair-orders/new" element={<RepairOrderCreatePage />} />
          <Route path="technician/repair-orders" element={<TechnicianRepairOrdersPage />} />
        </Route>

        <Route path="/" element={<Navigate to="/admin" replace />} />
        <Route path="*" element={<Navigate to="/admin" replace />} />
      </Routes>
    </>
  );
}