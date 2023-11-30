import {
  ArrowDownTrayIcon,
  InformationCircleIcon,
  ExclamationTriangleIcon,
  PaperAirplaneIcon,
} from "@heroicons/react/20/solid";
import { useDispatch, useSelector } from "react-redux";
import { t } from "../utils";
import {
  useUpdateModuleMutation,
  useUnlockModuleMutation,
} from "../services/service";
import Loading from "./Loading";
import toast from "react-hot-toast";
import { setLocks } from "../features/user/userSlice";

export default function Notice() {
  const systemStatus = useSelector((state) => state.user.systemStatus);
  const dispatcher = useDispatch();

  const [updateModule, { isLoading: updateModuleLoading }] =
    useUpdateModuleMutation();

  const [unlockModule, { isLoading: unlockModuleLoading }] =
    useUnlockModuleMutation();

  const updateHandler = () => {
    toast.promise(updateModule().unwrap(), {
      loading: t("Updating module..."),
      success: () => {
        window.location.reload(true);
        return t("Module updated successfully.");
      },
      error: t("Failed to update module."),
    });
  };

  const unlockHandler = () => {
    toast.promise(unlockModule().unwrap(), {
      loading: t("Unlocking and sending report..."),
      success: () => {
        dispatcher(setLocks([]));
        return t("Cronjob unlocked and report sent successfully.");
      },
      error: t("Failed to unlock and send report."),
    });
  };

  return (
    <div className="flex flex-col gap-2">
      {systemStatus.needUpdate && (
        <div className="rounded-md bg-blue-50 p-4">
          <div className="flex">
            <div className="flex-shrink-0">
              <InformationCircleIcon
                className="h-5 w-5 text-blue-400"
                aria-hidden="true"
              />
            </div>
            <div className="ml-3 flex-1 md:flex md:justify-between">
              <p className="text-sm text-blue-700">
                {t(
                  "A new version of module is available, update now to don't miss latest features."
                )}
              </p>
              <p className="mt-3 text-sm md:ml-6 md:mt-0">
                <button
                  type="button"
                  onClick={updateHandler}
                  disabled={updateModuleLoading}
                  className="whitespace-nowrap font-medium text-blue-700 hover:text-blue-600"
                >
                  {updateModuleLoading ? (
                    <Loading label={t("Updating module, wait...")} />
                  ) : (
                    <button
                      type="button"
                      className="inline-flex items-center gap-x-1.5 rounded-md  px-3 py-2 text-sm font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 as-btn-primary"
                    >
                      {t("Update now")}
                      <ArrowDownTrayIcon
                        className="-mr-0.5 h-5 w-5"
                        aria-hidden="true"
                      />
                    </button>
                  )}
                </button>
              </p>
            </div>
          </div>
        </div>
      )}{" "}
      {systemStatus.locks.length > 0 && (
        <div className="rounded-md bg-orange-50 p-4">
          <div className="flex">
            <div className="flex-shrink-0">
              <ExclamationTriangleIcon
                className="h-5 w-5 text-orange-400"
                aria-hidden="true"
              />
            </div>
            <div className="ml-3 flex-1 md:flex md:justify-between">
              <p className="text-sm text-orange-700">
                {t(
                  "Some cronjobs are locked for more than 60 minutes, usually this is caused by a fatal error during the executions, please check your error logs and system logs from Tab below for more details. You can also unlock and send a report to our support team."
                )}
              </p>
              <p className="mt-3 text-sm md:ml-6 md:mt-0">
                <button
                  type="button"
                  onClick={unlockHandler}
                  disabled={unlockModuleLoading}
                  className="whitespace-nowrap font-medium text-orange-700 hover:text-orange-600"
                >
                  {unlockModuleLoading ? (
                    <Loading label={t("Unlock and sending, wait...")} />
                  ) : (
                    <button
                      type="button"
                      className="inline-flex items-center gap-x-1.5 rounded-md  px-3 py-2 text-sm font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600  bg-orange-600 hover:bg-orange-500"
                    >
                      {t("Unlock and send report")}
                      <PaperAirplaneIcon
                        className="-mr-0.5 h-5 w-5"
                        aria-hidden="true"
                      />
                    </button>
                  )}
                </button>
              </p>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
