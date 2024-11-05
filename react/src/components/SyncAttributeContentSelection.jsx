import { t } from "../utils";
import { useUpdateConfigMutation } from "../services/service";
import toast from "react-hot-toast";
import { useGetAttributesQuery } from "../services/service";
import Loading from "./Loading";

export default function SyncAttributeContentSelection() {
  const { data: attributesData, isLoading } = useGetAttributesQuery();
  const psAttributes = attributesData?.data;

  const [updateConfig] = useUpdateConfigMutation();

  const handleSelect = (e) => {
    const { name, value } = e.target;
    toast.promise(
      updateConfig({
        [name]: value,
      }).unwrap(),
      {
        loading: t("Saving..."),
        success: t("Saved"),
        error: t("Error"),
      }
    );
  };

  return (
    <div>
      {isLoading ? (
        <Loading label={t("Loading...")} />
      ) : (
        <div className="grid grid-cols-2 gap-8">
          <div>
            <div>
              <label
                htmlFor="_ACCELASEARCH_COLOR_ID"
                className="block text-sm font-medium leading-6 text-gray-900"
              >
                {t("Select your color attribute")}
              </label>
              <select
                onChange={handleSelect}
                id="_ACCELASEARCH_COLOR_ID"
                name="_ACCELASEARCH_COLOR_ID"
                className="mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-blue-600 sm:text-sm sm:leading-6"
              >
                <option value={0}>{t("Don't sync")}</option>
                {psAttributes.map((attribute) => (
                  <option
                    key={attribute.id_attribute_group}
                    value={attribute.id_attribute_group}
                    selected={
                      parseInt(attribute.id_attribute_group) ===
                      parseInt(_AS.userStatus.attributes.color.id)
                    }
                  >
                    {attribute.name}
                  </option>
                ))}
              </select>
            </div>
          </div>
          <div>
            <div>
              <label
                htmlFor="_ACCELASEARCH_SIZE_ID"
                className="block text-sm font-medium leading-6 text-gray-900"
              >
                {t("Select your size attribute")}
              </label>
              <select
                onChange={handleSelect}
                id="_ACCELASEARCH_SIZE_ID"
                name="_ACCELASEARCH_SIZE_ID"
                className="mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-blue-600 sm:text-sm sm:leading-6"
              >
                <option value={0}>{t("Don't sync")}</option>
                {psAttributes.map((attribute) => (
                  <option
                    key={attribute.id_attribute_group}
                    value={attribute.id_attribute_group}
                    selected={
                      parseInt(attribute.id_attribute_group) ===
                      parseInt(_AS.userStatus.attributes.size.id)
                    }
                  >
                    {attribute.name}
                  </option>
                ))}
              </select>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
