import { t, cx } from "../utils";
import { useState } from "react";
import { RadioGroup } from "@headlessui/react";
import { CheckCircleIcon } from "@heroicons/react/20/solid";
import { useUpdateConfigMutation } from "../services/service";
import toast from "react-hot-toast";

const syncType = [
  {
    id: 1,
    title: t("Both configurable and simple products"),
    description: t(
      "Send to Accelasearch both configurable and simple products, simple products are displayed as variants of configurable products. This option only works for size and color attributes."
    ),
    slug: "CONFIGURABLE_WITH_SIMPLE",
  },
  {
    id: 2,
    title: t("Only configurable products"),
    description: t(
      "Send to Accelasearch only configurable products, variants of products will be ignored."
    ),
    slug: "CONFIGURABLE",
  },
  {
    id: 3,
    title: t("Only simple products"),
    description: t(
      "Send to Accelasearch only simple products, only each variant of products will be sent (this can be increase your product numbers)."
    ),
    slug: "SIMPLE",
  },
];

const syncPosition = syncType.findIndex(
  (sync) => sync.slug === _AS.userStatus.syncType
);

export default function SyncTypeSelection() {
  const [selectedSyncType, setSelectedSyncType] = useState(
    syncType[syncPosition]
  );

  const [updateConfig] = useUpdateConfigMutation();

  const selectType = (type) => {
    setSelectedSyncType(type);
    toast.promise(
      updateConfig({
        _ACCELASEARCH_SYNCTYPE: type.slug,
      }),
      {
        loading: t("Saving..."),
        success: t("Saved"),
        error: t("Error"),
      }
    );
  };

  return (
    <RadioGroup value={selectedSyncType} onChange={selectType}>
      <div className="grid grid-cols-1 gap-y-6 sm:grid-cols-3 sm:gap-x-4">
        {syncType.map((sync) => (
          <RadioGroup.Option
            key={sync.id}
            value={sync}
            className={({ active }) =>
              cx(
                active
                  ? "border-blue-600 ring-2 ring-blue-600"
                  : "border-gray-300",
                "relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none"
              )
            }
          >
            {({ checked, active }) => (
              <>
                <span className="flex flex-1">
                  <span className="flex flex-col">
                    <RadioGroup.Label
                      as="span"
                      className="block text-sm font-medium text-gray-900"
                    >
                      {sync.title}
                    </RadioGroup.Label>
                    <RadioGroup.Description
                      as="span"
                      className="mt-1 flex items-center text-sm text-gray-500"
                    >
                      {sync.description}
                    </RadioGroup.Description>
                    <RadioGroup.Description
                      as="span"
                      className="mt-6 text-sm font-medium text-gray-900"
                    ></RadioGroup.Description>
                  </span>
                </span>
                <CheckCircleIcon
                  className={cx(
                    !checked ? "invisible" : "",
                    "h-5 w-5 text-blue-600"
                  )}
                  aria-hidden="true"
                />
                <span
                  className={cx(
                    active ? "border" : "border-2",
                    checked ? "border-blue-600" : "border-transparent",
                    "pointer-events-none absolute -inset-px rounded-lg"
                  )}
                  aria-hidden="true"
                />
              </>
            )}
          </RadioGroup.Option>
        ))}
      </div>
    </RadioGroup>
  );
}
