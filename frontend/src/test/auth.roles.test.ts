import {
  canManageUsers,
  canAccessClients,
  canAccessTechnicianSpace,
  getDefaultAdminPath,
  getRoleLabel,
} from "../features/auth/auth.roles";

describe("auth.roles", () => {
  it("canManageUsers only for admin roles", () => {
    expect(canManageUsers("ROLE_SUPER_ADMIN")).toBe(true);
    expect(canManageUsers("ROLE_ADMIN")).toBe(true);
    expect(canManageUsers("ROLE_TECHNICIAN")).toBe(false);
  });

  it("canAccessClients for reception too", () => {
    expect(canAccessClients("ROLE_RECEPTION")).toBe(true);
  });

  it("canAccessTechnicianSpace only for technician", () => {
    expect(canAccessTechnicianSpace("ROLE_TECHNICIAN")).toBe(true);
    expect(canAccessTechnicianSpace("ROLE_ADMIN")).toBe(false);
  });

  it("getDefaultAdminPath returns technician path", () => {
    expect(getDefaultAdminPath("ROLE_TECHNICIAN")).toBe(
      "/admin/technician/repair-orders"
    );
  });

  it("getRoleLabel returns French label", () => {
    expect(getRoleLabel("ROLE_ADMIN")).toBe("Administrateur");
  });
});