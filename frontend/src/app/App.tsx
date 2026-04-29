import { GlobalLoadingOverlay } from "../shared/components/Loading/GlobalLoadingOverlay";
import AppRoutes from "./AppRoutes";

export default function App() {
  return (
    <>
      <GlobalLoadingOverlay />
      <AppRoutes />
    </>
  );
}
