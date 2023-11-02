import PropTypes from "prop-types";
import { t } from "../utils";
import ShopSelection from "../components/ShopSelection";
import SyncAttributeSelection from "../components/SyncAttributeSelection";

export default function OnBoardingPart({ number }) {
  return (
    <div>
      {number === 0 && <ShopSelection />}
      {number === 1 && <SyncAttributeSelection />}
      {number === 2 && <div>{t("Cronjob configuration")}</div>}
    </div>
  );
}

OnBoardingPart.propTypes = {
  number: PropTypes.number.isRequired,
};
