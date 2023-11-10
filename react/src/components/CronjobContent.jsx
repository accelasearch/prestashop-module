import { ClipboardIcon } from "@heroicons/react/20/solid";
import { t } from "../utils";

export default function CronjobContent() {
  const copyText = () => {
    const copyText = document.getElementById("cronjob_command");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand("copy");
  };

  return (
    <div>
      <div className="mt-12 flex rounded-md shadow-sm">
        <div className="relative flex flex-grow items-stretch focus-within:z-10">
          <input
            type="text"
            name="cronjob_command"
            id="cronjob_command"
            className="block w-full h-full rounded-none rounded-l-md border-0 py-1.5 text-gray-600 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
            placeholder="Cronjob command"
            value={`*/5 * * * * curl -fsSL "${_AS.userStatus.shopUrl}modules/accelasearch/cronManager.php?token=${_AS.userStatus.cronjobToken}"`}
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
          {t("Copy")}
        </button>
      </div>
    </div>
  );
}
