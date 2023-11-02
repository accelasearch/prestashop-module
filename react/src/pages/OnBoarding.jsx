import PropTypes from "prop-types";
import { t, cx } from "../utils";
import OnBoardingPart from "./OnBoardingPart";
import { setOnBoarding } from "../features/user/userSlice";
import { useDispatch } from "react-redux";

export default function OnBoarding({ number }) {
  const steps = [
    { id: "Step 1", name: t("Shop selection"), status: "upcoming" },
    { id: "Step 2", name: t("Sync type & Attributes"), status: "upcoming" },
    { id: "Step 3", name: t("Cronjob configuration"), status: "upcoming" },
  ];

  const dispatch = useDispatch();

  for (let i = 0; i < number; i++) {
    steps[i].status = "completed";
  }

  const changeStep = (number, step) => {
    if (step.status === "upcoming") return;
    const changeStep = confirm(
      t("If you go back you lost current changes, are you sure?")
    );
    if (changeStep) dispatch(setOnBoarding(number));
  };

  steps[number].status = "current";

  return (
    <div>
      <div>
        <img
          className="mb-4 h-8 w-auto"
          src={accelasearch_public_url + "as-logo.svg"}
          alt="AccelaSearch Logo"
        />
      </div>
      <nav aria-label="Progress">
        <ol role="list" className="space-y-4 md:flex md:space-x-8 md:space-y-0">
          {steps.map((step, i) => (
            <li key={step.name} className="md:flex-1">
              <button
                type="button"
                onClick={() => changeStep(i, step)}
                className={cx(
                  "flex flex-col border-l-4 py-2 pl-4 md:border-l-0 md:border-t-4 md:pb-0 md:pl-0 md:pt-4 w-full",
                  step.status === "upcoming" && "cursor-not-allowed",
                  step.status === "completed" && "opacity-50",
                  (step.status === "current" || step.status === "completed") &&
                    "border-blue-600"
                )}
                aria-current="step"
              >
                <span className="text-lg font-bold text-blue-600">
                  {step.id}
                </span>
                <span className="text-sm font-medium">{step.name}</span>
              </button>
            </li>
          ))}
        </ol>
      </nav>
      <div className="pt-12">
        <OnBoardingPart number={number} />
      </div>
    </div>
  );
}

OnBoarding.propTypes = {
  number: PropTypes.number.isRequired,
};
