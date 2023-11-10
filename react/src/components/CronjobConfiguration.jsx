import { useGetCronjobStatusQuery } from "../services/service";
import { t } from "../utils";
import { useDispatch } from "react-redux";
import { setOnBoarding } from "../features/user/userSlice";
import CronjobContent from "./CronjobContent";

export default function CronjobConfiguration() {
  const dispatch = useDispatch();

  const { data: cronjobData } = useGetCronjobStatusQuery(null, {
    pollingInterval: 5000,
  });

  const executed = cronjobData?.data?.executed;

  if (executed === true) {
    location.reload(true);
    dispatch(setOnBoarding(3));
  }

  return (
    <div>
      <p className="text-3xl font-bold text-zinc-800">
        {t(
          "Setup cronjob to run sync automatically, it's a mandatory step to complete the onboarding process."
        )}
      </p>
      <p>
        {t(
          "Once you have setup and executed the cronjob, this screen will be updated automatically and onboarding process will be completed."
        )}
      </p>
      <div>
        <CronjobContent />
      </div>
    </div>
  );
}
