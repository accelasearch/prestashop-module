import { useGetAttributesQuery } from "../services/service";
import { t } from "../utils";
import Loading from "./Loading";
import SyncTypeSelection from "./SyncTypeSelection";

export default function SyncAttributeSelection() {
  const { data: attributesData, isLoading } = useGetAttributesQuery();
  const psAttributes = attributesData?.data;

  return (
    <div>
      <div>
        <h2 className="mt-6 text-xl font-bold text-gray-900">
          {t("Select your synchronization type")}
        </h2>
        <div className="mt-6">
          <SyncTypeSelection />
        </div>
      </div>
      <div className="grid grid-cols-2 gap-8">
        <div className="col-span-2">
          <h2 className="mt-6 text-xl font-bold text-gray-900">
            {t(
              "Map your existing attributes for each language to AccelaSearch attributes color/size, if you don't want to manage these attributes leave it empty"
            )}
          </h2>
        </div>
        {isLoading ? (
          <Loading label={t("Loading...")} />
        ) : (
          psAttributes.map(({ language, attributes }) => (
            <div key={language.id_lang}>
              <p className="text-lg font-semibold text-gray-800">
                {language.name}
              </p>
              <div className="mb-4">
                <div>
                  <label
                    htmlFor="color_attribute"
                    className="block text-sm font-medium leading-6 text-gray-900"
                  >
                    {t("Select your color attribute")}
                  </label>
                  <select
                    id="color_attribute"
                    name="color_attribute"
                    className="mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  >
                    {attributes.map((attribute) => (
                      <option key={attribute.id_attribute_group}>
                        {attribute.name}
                      </option>
                    ))}
                  </select>
                </div>
              </div>
              <div>
                <div>
                  <label
                    htmlFor="size_attribute"
                    className="block text-sm font-medium leading-6 text-gray-900"
                  >
                    {t("Select your size attribute")}
                  </label>
                  <select
                    id="size_attribute"
                    name="size_attribute"
                    className="mt-2 block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6"
                  >
                    {attributes.map((attribute) => (
                      <option key={attribute.id_attribute_group}>
                        {attribute.name}
                      </option>
                    ))}
                  </select>
                </div>
              </div>
            </div>
          ))
        )}
      </div>
    </div>
  );
}
