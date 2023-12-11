import { t, cx } from "../utils";
import SyncAttributeContentSelection from "./SyncAttributeContentSelection";
import SyncTypeSelection from "./SyncTypeSelection";
import { useDispatch } from "react-redux";
import { useUpdateConfigMutation } from "../services/service";
import { setOnBoarding } from "../features/user/userSlice";
import toast from "react-hot-toast";
import PropTypes from "prop-types";

export default function SyncAttributeSelection({
  withSubmit = true,
  isOnBoarding = true,
}) {
  const [updateConfig, { isLoading: isSyncSubmitting }] =
    useUpdateConfigMutation();
  const dispatch = useDispatch();

  const handleSyncSubmit = () => {
    toast.promise(
      updateConfig({
        _ACCELASEARCH_ONBOARDING: 2,
      }),
      {
        loading: t("Saving..."),
        success: () => {
          dispatch(setOnBoarding(2));
          return t("Saved");
        },
        error: t("Error"),
      }
    );
  };

  return (
    <div>
      <div>
        <h2 className="mt-6 text-xl font-bold text-gray-900">
          {t("Select your synchronization type")}
        </h2>
        <div className="mt-6">
          <SyncTypeSelection isOnBoarding={isOnBoarding} />
        </div>
      </div>
      <div>
        <div>
          <h2 className="mt-6 text-xl font-bold text-gray-900">
            {t(
              "Map your existing attributes to AccelaSearch attributes color/size, if you don't want to manage these attributes select the option 'Don't sync'"
            )}
          </h2>
        </div>
        <SyncAttributeContentSelection />
      </div>
      {withSubmit && (
        <div className="flex items-center justify-center mt-12">
          <button
            type="button"
            onClick={handleSyncSubmit}
            disabled={isSyncSubmitting}
            className={cx(
              "as-btn-primary max-w-sm",
              isSyncSubmitting && "cursor-not-allowed"
            )}
          >
            {t("Go to cronjob configuration")}
          </button>
        </div>
      )}
    </div>
  );
}

SyncAttributeSelection.propTypes = {
  withSubmit: PropTypes.bool,
  isOnBoarding: PropTypes.bool,
};
