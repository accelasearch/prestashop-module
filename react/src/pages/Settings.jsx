import {
  BuildingStorefrontIcon,
  ClockIcon,
  MagnifyingGlassCircleIcon,
} from "@heroicons/react/24/outline";
import Card from "../components/Card";
import { t } from "../utils";
import ShopSelection from "../components/ShopSelection";
import SyncAttributeSelection from "../components/SyncAttributeSelection";

export default function Settings() {
  return (
    <div>
      <div className="md:grid grid-cols-3 gap-8">
        <Card
          Icon={ClockIcon}
          title={_AS.userStatus.lastExec}
          description={t("Last cronjob execution time")}
        />
        <Card
          Icon={BuildingStorefrontIcon}
          title={_AS.userStatus.shops.length}
          description={t("Shops/Languages synced")}
        />
        <Card
          Icon={MagnifyingGlassCircleIcon}
          title={_AS.userStatus.exportedProductsCount}
          description={t("Exported products (based on your configuration)")}
        />
      </div>
      <div className="mt-12">
        <ShopSelection isOnBoarding={true} />
      </div>
      <div className="mt-12">
        <SyncAttributeSelection withSubmit={false} />
      </div>
    </div>
  );
}
