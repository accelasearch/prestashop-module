import PropTypes from "prop-types";
import ShopSelection from "../components/ShopSelection";
import SyncAttributeSelection from "../components/SyncAttributeSelection";
import CronjobConfiguration from "../components/CronjobConfiguration";

export default function OnBoardingPart({ number }) {
  return (
    <div>
      {number === 0 && <ShopSelection isOnBoarding={true} />}
      {number === 1 && <SyncAttributeSelection />}
      {number === 2 && <CronjobConfiguration />}
    </div>
  );
}

OnBoardingPart.propTypes = {
  number: PropTypes.number.isRequired,
};
