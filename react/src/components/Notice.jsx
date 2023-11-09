import { InformationCircleIcon } from "@heroicons/react/20/solid";
import { useSelector } from "react-redux";
import { t } from "../utils";
import { useUpdateModuleMutation } from "../services/service";
import Loading from "./Loading";
import toast from "react-hot-toast";

export default function Notice() {
  const systemStatus = useSelector((state) => state.user.systemStatus);

  const [updateModule, { isLoading: updateModuleLoading }] =
    useUpdateModuleMutation();

  const updateHandler = () => {
    toast.promise(updateModule(), {
      loading: t("Updating module..."),
      success: () => {
        window.location.reload(true);
        return t("Module updated successfully.");
      },
      error: t("Failed to update module."),
    });
  };

  return (
    systemStatus.needUpdate && (
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
                  <span>
                    {t("Update now")}
                    <span aria-hidden="true"> &rarr;</span>
                  </span>
                )}
              </button>
            </p>
          </div>
        </div>
      </div>
    )
  );
}
