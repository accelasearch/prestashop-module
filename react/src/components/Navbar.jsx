import { t, cx } from "../utils";
import { Fragment } from "react";
import { Menu, Transition } from "@headlessui/react";
import { ArrowTopRightOnSquareIcon, UserIcon } from "@heroicons/react/20/solid";
import { useDispatch, useSelector } from "react-redux";
import { setPage } from "../features/user/userSlice";
import { useDisconnectMutation } from "../services/service";
import toast from "react-hot-toast";

export default function Navbar() {
  const menu = [
    { name: t("Settings") },
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
          <Menu as="div" className="relative ml-3">
            <div>
              <Menu.Button className="relative flex rounded-full bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 w-8 h-8">
                <span className="absolute -inset-1.5" />
                <span className="sr-only">Open user menu</span>
                <UserIcon />
              </Menu.Button>
            </div>
            <Transition
              as={Fragment}
              enter="transition ease-out duration-200"
              enterFrom="transform opacity-0 scale-95"
              enterTo="transform opacity-100 scale-100"
              leave="transition ease-in duration-75"
              leaveFrom="transform opacity-100 scale-100"
              leaveTo="transform opacity-0 scale-95"
            >
              <Menu.Items className="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                <Menu.Item>
                  {({ active }) => (
                    <a
                      target="_blank"
                      rel="noreferrer"
                      href="https://console.accelasearch.io/dashboard"
                      className={cx(
                        active ? "bg-gray-100" : "",
                        "block px-4 py-2 text-sm text-gray-700"
                      )}
                    >
                      {t("Go to Accelasearch Dashboard")}
                      <ArrowTopRightOnSquareIcon className="inline-block ml-2 h-4 w-4" />
                    </a>
                  )}
                </Menu.Item>
                <Menu.Item>
                  {({ active }) => (
                    <button
                      type="button"
                      onClick={handleDisconnect}
                      className={cx(
                        active ? "bg-gray-100" : "",
                        "block px-4 py-2 text-sm text-red-700 w-full text-left"
                      )}
                    >
                      {t("Disconnect")}
                    </button>
                  )}
                </Menu.Item>
              </Menu.Items>
            </Transition>
          </Menu>
        </div>
      </div>
    </div>
  );
}
