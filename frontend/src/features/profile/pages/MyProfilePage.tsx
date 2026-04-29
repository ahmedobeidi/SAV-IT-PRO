import { Link } from "react-router-dom";
import { ArrowLeft } from "lucide-react";

import ChangeMyPasswordForm from "../components/ChangeMyPasswordForm";

export default function MyProfilePage() {
  return (
    <div className="form-shell">
      <div className="form-shell-inner">
        {/* Header with Retour */}
        <div className="form-header">
          <Link
            to="/admin"
            className="btn form-back-link"
          >
            <ArrowLeft size={16} />
            Retour
          </Link>

          <div className="form-header-title">
            <h2 className="page-title">Mon profil</h2>
          </div>

          {/* spacer to keep title centered */}
          <div className="form-header-spacer" />
        </div>

        <ChangeMyPasswordForm />
      </div>
    </div>
  );
}
