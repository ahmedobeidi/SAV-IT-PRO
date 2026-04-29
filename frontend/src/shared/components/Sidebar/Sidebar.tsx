import { NavLink, useMatch } from "react-router-dom";
import {
  LayoutDashboard,
  Users,
  Wrench,
  ClipboardList,
  UserCog,
  User,
} from "lucide-react";
import { authStore } from "../../../features/auth/auth.store";
import {
  canManageUsers,
  canAccessClients,
  canAccessEquipment,
  canAccessRepairs,
  canAccessTechnicianSpace,
} from "../../../features/auth/auth.roles";
import "./Sidebar.css";
import logo2 from "../../assets/Logo2.svg";
import { APP_PATHS } from "../../../app/paths";

export default function Sidebar() {
  const { role } = authStore.getTokens();
  const equipmentMatch = useMatch("/admin/equipment/*");

  const canSeeUsers = canManageUsers(role);
  const canSeeClients = canAccessClients(role);
  const canSeeEquipment = canAccessEquipment(role);
  const canSeeRepairs = canAccessRepairs(role);
  const canSeeTechnicianPage = canAccessTechnicianSpace(role);

  const isEquipmentActive = !!equipmentMatch;

  return (
    <aside className="sidebar">
      <div className="sidebar__brand">
        <img src={logo2} alt="IT-PRO" className="sidebar__logo" />

        <div className="sidebar__brand-text">
          <div className="sidebar__brand-title">IT-PRO</div>
          <div className="sidebar__brand-desc">Informatique & Téléphones</div>
        </div>
      </div>

      <nav className="sidebar__nav">
        <div className="sidebar__section">GÉNÉRAL</div>

        <NavLink
          to={APP_PATHS.admin}
          end
          className={({ isActive }) =>
            `sidebar__link ${isActive ? "sidebar__link--active" : ""}`
          }
        >
          <LayoutDashboard size={18} />
          <span>Tableau de bord</span>
        </NavLink>

        <NavLink
          to={APP_PATHS.profile}
          className={({ isActive }) =>
            `sidebar__link ${isActive ? "sidebar__link--active" : ""}`
          }
        >
          <User size={18} />
          <span>Profil</span>
        </NavLink>

        {(canSeeUsers || canSeeClients) && (
          <div className="sidebar__section">GESTION</div>
        )}

        {canSeeUsers && (
          <NavLink
            to={APP_PATHS.users}
            className={({ isActive }) =>
              `sidebar__link ${isActive ? "sidebar__link--active" : ""}`
            }
          >
            <Users size={18} />
            <span>Employés</span>
          </NavLink>
        )}

        {canSeeClients && (
          <NavLink
            to={APP_PATHS.clients}
            className={({ isActive }) =>
              `sidebar__link ${isActive ? "sidebar__link--active" : ""}`
            }
          >
            <Users size={18} />
            <span>Clients</span>
          </NavLink>
        )}

        {(canSeeEquipment || canSeeRepairs || canSeeTechnicianPage) && (
          <div className="sidebar__section">OPÉRATIONS</div>
        )}

        {canSeeEquipment && (
          <NavLink
            to={APP_PATHS.equipmentTypes}
            className={({ isActive }) =>
              `sidebar__link ${
                isActive || isEquipmentActive ? "sidebar__link--active" : ""
              }`
            }
          >
            <Wrench size={18} />
            <span>Équipements</span>
          </NavLink>
        )}

        {canSeeRepairs && (
          <NavLink
            to={APP_PATHS.repairOrders}
            className={({ isActive }) =>
              `sidebar__link ${isActive ? "sidebar__link--active" : ""}`
            }
          >
            <ClipboardList size={18} />
            <span>Réparations</span>
          </NavLink>
        )}

        {canSeeTechnicianPage && (
          <NavLink
            to={APP_PATHS.technicianRepairOrders}
            className={({ isActive }) =>
              `sidebar__link ${isActive ? "sidebar__link--active" : ""}`
            }
          >
            <UserCog size={18} />
            <span>Technicien</span>
          </NavLink>
        )}
      </nav>
    </aside>
  );
}
