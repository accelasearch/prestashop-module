import { t } from "../utils";
import { useSelector } from "react-redux";
import OnBoarding from "./OnBoarding";

export default function AsApp() {
  const { userStatus } = useSelector((state) => state.user);
  const { onBoarding } = userStatus;

  return (
    <div>
      {onBoarding < 4 && <OnBoarding number={parseInt(onBoarding)} />}
      {onBoarding === 4 && <div>Onboarding finito</div>}
    </div>
  );
}
