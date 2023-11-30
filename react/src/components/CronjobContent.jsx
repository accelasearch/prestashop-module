import { ClipboardIcon, ChevronDownIcon } from "@heroicons/react/20/solid";
import { cx, t } from "../utils";
import React from "react";

export default function CronjobContent() {
  const copyText = () => {
    const copyText = document.getElementById("cronjob_command");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand("copy");
  };

  const [open, setOpen] = React.useState(false);

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
      <div className="mt-8">
        <div>
          <button
            onClick={() => setOpen(!open)}
            type="button"
            className="inline-flex justify-center gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm hover:bg-gray-50"
          >
            {t("Advanced usage")}
            <ChevronDownIcon
              className={cx(
                "-mr-1 h-5 w-5 text-gray-400 transition-all",
                open && "-rotate-180"
              )}
              aria-hidden="true"
            />
          </button>
          {open && (
            <div className="p-2 mt-4">
              <p>
                {t(
                  "You can run a feed generation command per shop/language directly thanks to CLI commands"
                )}
              </p>
              <p>
                {t("Starting from accelasearch module folder, you can run:")}
              </p>
              <div className="w-full mt-4">
                <div
                  className="coding inverse-toggle px-5 shadow-lg text-gray-100 text-sm font-mono subpixel-antialiased 
              bg-gray-800  pb-6 pt-4 rounded-lg leading-normal overflow-hidden"
                >
                  <div className="top mb-2 flex">
                    <div className="h-3 w-3 bg-red-500 rounded-full"></div>
                    <div className="ml-2 h-3 w-3 bg-orange-300 rounded-full"></div>
                    <div className="ml-2 h-3 w-3 bg-green-500 rounded-full"></div>
                  </div>
                  <div className="mt-4 flex">
                    <span className="text-green-400">root:~$</span>
                    <p className="flex-1 typing items-center pl-2">
                      php bin/feed {"<id_shop> <id_lang>"}
                      <br />
                    </p>
                  </div>
                </div>
              </div>
              <p className="mt-2">
                {t(
                  "Where id_shop and id_lang are the shop and language ids you want to generate."
                )}
              </p>
              <p className="mt-2">
                {t(
                  "This let you to replace your cronjob command with a more precise and segmented one valid for data feed generation. It also can be faster depending on your server configuration - shops/languages and products."
                )}
              </p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
