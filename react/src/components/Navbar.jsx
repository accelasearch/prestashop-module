import { t, cx } from "../utils";
import { PowerIcon } from "@heroicons/react/20/solid";
import { useDispatch, useSelector } from "react-redux";
import { setPage } from "../features/user/userSlice";
import { useDisconnectMutation } from "../services/service";
import toast from "react-hot-toast";

export default function Navbar() {
  const menu = [
    { name: t("Settings") },
    { name: t("Cronjob") },
    { name: t("Logs") },
    { name: t("Help") },
  ];

  const dispatch = useDispatch();
  const page = useSelector((state) => state.user.page);

  const [disconnect] = useDisconnectMutation();

  const handleDisconnect = () => {
    if (
      !confirm(
        t(
          "Are you sure you want to disconnect? You will be redirected to welcome page and lost all configurations from Prestashop side."
        )
      )
    )
      return;
    toast.promise(disconnect(), {
      loading: t("Disconnecting..."),
      success: () => {
        location.reload(true);
        return t("Disconnected successfully");
      },
      error: t("An error occurred during disconnect."),
    });
  };

  return (
    <div>
      <div className="flex h-16 justify-between">
        <div className="flex">
          <div className="flex flex-shrink-0 items-center">
            <img
              className="h-8 w-auto"
              src={accelasearch_public_url + "as_sfondo.svg"}
              alt="Accelasearch logo"
            />
          </div>
          <div className="flex sm:pl-6">
            {menu.map((item) => (
              <button
                key={item.name}
                type="button"
                onClick={() => dispatch(setPage(item))}
                className={cx(
                  item.name === page.name
                    ? "border-blue-500 text-blue-500 hover:!text-gray-900 hover:!border-blue-500"
                    : "border-transparent text-gray-500",
                  "inline-flex items-center px-8 pt-1 text-sm font-medium border-b-2 hover:border-gray-300 hover:text-gray-700"
                )}
              >
                {item.name}
              </button>
            ))}
          </div>
        </div>
        <div className="sm:ml-6 sm:flex sm:items-center">
          <button
            type="button"
            onClick={() => handleDisconnect()}
            className="inline-flex items-center gap-x-1.5 rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600"
          >
            <PowerIcon className="-ml-0.5 h-5 w-5" aria-hidden="true" />
            {t("Disconnect")}
          </button>
        </div>
      </div>
    </div>
  );
}
