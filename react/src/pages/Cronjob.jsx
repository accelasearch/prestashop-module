import { ExclamationTriangleIcon } from "@heroicons/react/20/solid";
import CronjobContent from "../components/CronjobContent";
import { t } from "../utils";

export default function Cronjob() {
  return (
    <div>
      <p className="text-3xl font-bold text-zinc-800">{t("Cronjob setup")}</p>
      <div className="rounded-md bg-yellow-50 p-4 mt-8">
        <div className="flex">
          <div className="flex-shrink-0">
            <ExclamationTriangleIcon
              className="h-5 w-5 text-yellow-400"
              aria-hidden="true"
            />
          </div>
          <div className="ml-3">
            <p className="text-sm font-medium text-yellow-800">
              {t("Attention to Cronjob Token")}
            </p>
            <div className="mt-2 text-sm text-yellow-700">
              <p>
                {t(
                  "Cronjob token will change on disconnect and will be regenerated, don't forget to update your cronjob command."
                )}
              </p>
            </div>
          </div>
        </div>
      </div>
      <div>
        <CronjobContent />
      </div>
    </div>
  );
}
