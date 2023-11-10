import { useSelector } from "react-redux";
import OnBoarding from "./OnBoarding";
import Dashboard from "../components/Dashboard";

export default function AsApp() {
  const { userStatus } = useSelector((state) => state.user);
  const { onBoarding } = userStatus;

  return (
    <div>
      {onBoarding < 3 ? (
        <OnBoarding number={parseInt(onBoarding)} />
      ) : (
        <Dashboard />
      )}
    </div>
  );
}
