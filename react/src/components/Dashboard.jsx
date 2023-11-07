import Help from "../pages/Help";
import Settings from "../pages/Settings";
import Logs from "../pages/Logs";
import Navbar from "./Navbar";
import { useSelector } from "react-redux";

export default function Dashboard() {
  const page = useSelector((state) => state.user.page);

  return (
    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <Navbar />
      <div className="py-12">
        {page.name === "Settings" && <Settings />}
        {page.name === "Logs" && <Logs />}
        {page.name === "Help" && <Help />}
      </div>
    </div>
  );
}
