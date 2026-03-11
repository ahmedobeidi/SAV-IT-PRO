import ChangeMyPasswordForm from "../components/ChangeMyPasswordForm";

export default function MyProfilePage() {
  return (
    <div style={{ padding: 16, display: "grid", gap: 16 }}>
      <h2>Mon profil</h2>
      <ChangeMyPasswordForm />
    </div>
  );
}