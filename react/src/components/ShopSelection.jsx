import { cx, t } from "../utils";
import React from "react";
import { useGetShopsQuery, useSetShopsMutation } from "../services/service";
import { useDispatch } from "react-redux";
import { setOnBoarding } from "../features/user/userSlice";
import toast from "react-hot-toast";
import Loading from "./Loading";

export default function ShopSelection() {
  const [shopsToSync, setShopsToSync] = React.useState([]);

  const { data: shopsDataQuery, isLoading, isError } = useGetShopsQuery();
  const dispatch = useDispatch();
  const shops = shopsDataQuery?.data || [];

  const [setShops, { isLoading: isSettingShops }] = useSetShopsMutation();

  const handleCheckboxChange = (shop) => {
    return (event) => {
      if (event.target.checked) {
        setShopsToSync([...shopsToSync, shop]);
      } else {
        setShopsToSync(shopsToSync.filter((s) => s !== shop));
      }
    };
  };

  const handleShopSelectionSubmit = () => {
    toast.promise(setShops(shopsToSync), {
      loading: t("Adding shops to sync..."),
      success: () => {
        dispatch(setOnBoarding(1));
        return t("Shops selected successfully");
      },
      error: t("An error occurred during sync your shops."),
    });
  };

  return (
    <div>
      <p className="text-3xl font-bold leading-6 text-zinc-800">
        {t("Select the shops/languages you want to sync on AccelaSearch")}
      </p>
      {isError && (
        <div>{t("An error occurred during load your shops.")}...</div>
      )}
      {isLoading ? (
        <div className="mt-12">
          <Loading label={t("Loading...")} />
        </div>
      ) : (
        <ul className="mt-12 grid w-full gap-6 md:grid-cols-3">
          {shops.map((shop) => (
            <li key={`shop-${shop.id_shop}-${shop.id_lang}`}>
              <input
                type="checkbox"
                id={`shop-${shop.id_shop}-${shop.id_lang}`}
                value={`${shop.id_shop}-${shop.id_lang}`}
                name="shops_to_sync[]"
                className="hidden peer"
                onChange={handleCheckboxChange(shop)}
              />
              <label
                htmlFor={`shop-${shop.id_shop}-${shop.id_lang}`}
                className="inline-flex items-center justify-between w-full p-5 text-gray-500 bg-white border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-blue-600 hover:text-gray-600 peer-checked:text-gray-600 hover:bg-gray-50"
              >
                <div className="block">
                  <img src={shop.flagIcon} alt={shop.name} />
                  <div className="w-full text-lg font-semibold mt-8">
                    {shop.name}
                  </div>
                </div>
              </label>
            </li>
          ))}
        </ul>
      )}
      <div className="flex items-center justify-center mt-12">
        <button
          type="button"
          onClick={handleShopSelectionSubmit}
          className={cx(
            "as-btn-primary max-w-sm",
            (isLoading || shopsToSync.length === 0 || isSettingShops) &&
              "cursor-not-allowed"
          )}
        >
          {t("Synchronize")} {shopsToSync.length} {t("shops to Accelasearch")}
        </button>
      </div>
    </div>
  );
}
