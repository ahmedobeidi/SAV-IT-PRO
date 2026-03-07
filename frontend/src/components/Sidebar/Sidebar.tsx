import { NavLink, useMatch } from "react-router-dom";
import {
  LayoutDashboard,
  Users,
  Wrench,
  ClipboardList,
  UserCog,
} from "lucide-react";
import { authStore } from "../../auth/auth.store.ts";
import "./Sidebar.css";
import logo2 from "../../assets/logo2.svg";

export default function Sidebar() {
  const { role } = authStore.getTokens();
  const equipmentMatch = useMatch("/admin/equipment/*");

  const canManageUsers =
    role === "Super Administrateur" || role === "Administrateur";

  const isEquipmentActive = !!equipmentMatch;

  return (
    <aside className="sidebar">
      <div className="sidebar__brand">
        <img
          src={logo2}
          alt="IT-PRO"
          className="sidebar__logo"
        />

        <div className="sidebar__brand-text">
          <div className="sidebar__brand-title">IT-PRO</div>
          <div className="sidebar__brand-desc">Informatique & Téléphones</div>
        </div>
      </div>

      <nav className="sidebar__nav">
        <NavLink
          to="/admin"
          end
          className={({ isActive }) =>
            `sidebar__link ${isActive ? "sidebar__link--active" : ""}`
          }
        >
          <LayoutDashboard size={18} />
          <span>Tableau de bord</span>
        </NavLink>

        {canManageUsers && (
          <>
            <div className="sidebar__section">Gestion des utilisateurs</div>

            <NavLink
              to="/admin/users"
              className={({ isActive }) =>
                `sidebar__link ${isActive ? "sidebar__link--active" : ""}`
              }
            >
              <Users size={18} />
              <span>Employés</span>
            </NavLink>
          </>
        )}

        <div className="sidebar__section">Gestion des clients</div>

        <NavLink
          to="/admin/clients"
          className={({ isActive }) =>
            `sidebar__link ${isActive ? "sidebar__link--active" : ""}`
          }
        >
          <Users size={18} />
          <span>Clients</span>
        </NavLink>

        <div className="sidebar__section">Gestion des opérations</div>

        <NavLink
          to="/admin/equipment/types"
          className={({ isActive }) =>
            `sidebar__link ${isActive || isEquipmentActive ? "sidebar__link--active" : ""
            }`
          }
        >
          <Wrench size={18} />
          <span>Équipements</span>
        </NavLink>

        <NavLink
          to="/admin/repair-orders"
          className={({ isActive }) =>
            `sidebar__link ${isActive ? "sidebar__link--active" : ""}`
          }
        >
          <ClipboardList size={18} />
          <span>Réparations</span>
        </NavLink>

        <NavLink
          to="/admin/technician/repair-orders"
          className={({ isActive }) =>
            `sidebar__link ${isActive ? "sidebar__link--active" : ""}`
          }
        >
          <UserCog size={18} />
          <span>Espace technicien</span>
        </NavLink>
      </nav>
    </aside>
  );
}