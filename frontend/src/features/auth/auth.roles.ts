export type AppRole =
  | "ROLE_SUPER_ADMIN"
  | "ROLE_ADMIN"
  | "ROLE_TECHNICIAN"
  | "ROLE_RECEPTION"
  | null;

function normalizeRole(raw: string | null): AppRole {
  if (!raw) return null;

  const value = raw.trim();

  switch (value) {
    case "ROLE_SUPER_ADMIN":
    case "Super Administrateur":
      return "ROLE_SUPER_ADMIN";

    case "ROLE_ADMIN":
    case "Administrateur":
      return "ROLE_ADMIN";

    case "ROLE_TECHNICIAN":
    case "Technicien":
      return "ROLE_TECHNICIAN";

    case "ROLE_RECEPTION":
    case "Réception":
      return "ROLE_RECEPTION";

    default:
      return null;
  }
}

export function getNormalizedRole(raw: string | null): AppRole {
  return normalizeRole(raw);
}

export function isTechnician(raw: string | null): boolean {
  return normalizeRole(raw) === "ROLE_TECHNICIAN";
}

export function canManageUsers(raw: string | null): boolean {
  const role = normalizeRole(raw);
  return role === "ROLE_SUPER_ADMIN" || role === "ROLE_ADMIN";
}

export function canAccessClients(raw: string | null): boolean {
  const role = normalizeRole(raw);
  return (
    role === "ROLE_SUPER_ADMIN" ||
    role === "ROLE_ADMIN" ||
    role === "ROLE_RECEPTION"
  );
}

export function canAccessEquipment(raw: string | null): boolean {
  const role = normalizeRole(raw);
  return (
    role === "ROLE_SUPER_ADMIN" ||
    role === "ROLE_ADMIN" ||
    role === "ROLE_RECEPTION"
  );
}

export function canAccessRepairs(raw: string | null): boolean {
  const role = normalizeRole(raw);
  return (
    role === "ROLE_SUPER_ADMIN" ||
    role === "ROLE_ADMIN" ||
    role === "ROLE_RECEPTION"
  );
}

export function canAssignTechnician(raw: string | null): boolean {
  const role = normalizeRole(raw);
  return role === "ROLE_SUPER_ADMIN" || role === "ROLE_ADMIN";
}

export function canAccessTechnicianSpace(raw: string | null): boolean {
  return normalizeRole(raw) === "ROLE_TECHNICIAN";
}

export function canAccessDashboard(raw: string | null): boolean {
  return normalizeRole(raw) !== null;
}

export function canAccessProfile(raw: string | null): boolean {
  return normalizeRole(raw) !== null;
}

export function getDefaultAdminPath(raw: string | null): string {
  const role = normalizeRole(raw);

  switch (role) {
    case "ROLE_TECHNICIAN":
      return "/admin/technician/repair-orders";
    case "ROLE_RECEPTION":
    case "ROLE_ADMIN":
    case "ROLE_SUPER_ADMIN":
      return "/admin";
    default:
      return "/login";
  }
}

export function getRoleLabel(raw: string | null): string {
  const role = normalizeRole(raw);

  switch (role) {
    case "ROLE_SUPER_ADMIN":
      return "Super Administrateur";
    case "ROLE_ADMIN":
      return "Administrateur";
    case "ROLE_TECHNICIAN":
      return "Technicien";
    case "ROLE_RECEPTION":
      return "Réception";
    default:
      return "Administration";
  }
}