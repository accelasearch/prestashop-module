import Help from "../pages/Help";
import Settings from "../pages/Settings";
import Logs from "../pages/Logs";
import Navbar from "./Navbar";
import { useSelector } from "react-redux";
import Notice from "./Notice";
import { t } from "../utils";
import Cronjob from "../pages/Cronjob";

export default function Dashboard() {
  const page = useSelector((state) => state.user.page);

  return (
    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <Navbar />
      <div className="py-8">
        <Notice />
        <div className="py-4">
          {page.name === t("Settings") && <Settings />}
          {page.name === t("Logs") && <Logs />}
          {page.name === t("Help") && <Help />}
          {page.name === t("Cronjob") && <Cronjob />}
        </div>
      </div>
    </div>
  );
}
