import { Link, useNavigate } from "react-router-dom";
import { ArrowLeft } from "lucide-react";

import UserForm from "../components/UserForm";
import { usersApi } from "../users.api";
import type { CreateUserPayload } from "../users.types";

export default function UserCreatePage() {
  const navigate = useNavigate();

  return (
    <div className="form-shell">
      <div className="form-shell-inner">
        <div className="form-header">
          <Link
            to="/admin/users"
            className="btn form-back-link"
          >
            <ArrowLeft size={16} />
            Retour
          </Link>

          <div className="form-header-title">
            <h2 className="page-title">Créer un employé</h2>
          </div>

          <div className="form-header-spacer" />
        </div>

        <UserForm
          mode="create"
          onSubmit={async (payload) => {
            await usersApi.create(payload as CreateUserPayload);

            navigate("/admin/users", {
              state: {
                success:
                  "Employé créé avec succès.",
              },
            });
          }}
        />
      </div>
    </div>
  );
}
