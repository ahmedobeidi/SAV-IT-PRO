export const APP_PATHS = {
  root: "/",
  login: "/login",
  forgotPassword: "/forgot-password",
  resetPassword: "/reset-password",
  admin: "/admin",
  profile: "/admin/profile",
  users: "/admin/users",
  usersNew: "/admin/users/new",
  userEdit: (id: string | number = ":id") => `/admin/users/${id}/edit`,
  clients: "/admin/clients",
  clientsNew: "/admin/clients/new",
  clientEdit: (id: string | number = ":id") => `/admin/clients/${id}/edit`,
  equipmentTypes: "/admin/equipment/types",
  equipmentBrands: (typeId: string | number = ":typeId") =>
    `/admin/equipment/types/${typeId}/brands`,
  equipmentModels: (
    typeId: string | number = ":typeId",
    brandId: string | number = ":brandId",
  ) => `/admin/equipment/types/${typeId}/brands/${brandId}/models`,
  repairOrders: "/admin/repair-orders",
  repairOrdersNew: "/admin/repair-orders/new",
  technicianRepairOrders: "/admin/technician/repair-orders",
} as const;
