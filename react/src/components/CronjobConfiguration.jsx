import { useGetCronjobStatusQuery } from "../services/service";
import { t } from "../utils";
import { useDispatch } from "react-redux";
import { ClipboardIcon } from "@heroicons/react/20/solid";
import { setOnBoarding } from "../features/user/userSlice";

export default function CronjobConfiguration() {
  const dispatch = useDispatch();
  const copyText = () => {
    const copyText = document.getElementById("cronjob_command");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand("copy");
  };

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
        <div>
          <div className="mt-12 flex rounded-md shadow-sm">
            <div className="relative flex flex-grow items-stretch focus-within:z-10">
              <input
                type="text"
                name="cronjob_command"
                id="cronjob_command"
                className="block w-full h-full rounded-none rounded-l-md border-0 py-1.5 text-gray-600 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
                placeholder="Cronjob command"
                value={`*/15 * * * * ${_AS.userStatus.moduleDir}bin/feed`}
              />
            </div>
            <button
              type="button"
              onClick={copyText}
              className="relative -ml-px inline-flex items-center gap-x-1.5 rounded-r-md px-3 py-2 text-sm font-semibold text-gray-600 ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
            >
              <ClipboardIcon
                className="-ml-0.5 h-5 w-5 text-gray-400"
                aria-hidden="true"
              />
              Copy
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
