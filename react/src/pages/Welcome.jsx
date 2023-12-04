import { cx, t } from "../utils";
import React from "react";
import { toast } from "react-hot-toast";
import { useApikeyVerifyMutation } from "../services/service";

export default function Welcome() {
  const [started, setStarted] = React.useState(false);

  const [apikeyVerify, { isLoading: isVerifying }] = useApikeyVerifyMutation();
  const apikeyRef = React.useRef();

  const apikeySubmit = async (apikey) => {
    const { data, success } = await toast.promise(
      apikeyVerify(apikey).unwrap(),
      {
        loading: t("Verifying ApiKey..."),
        success: t("Your ApiKey is valid! Redirecting..."),
        error: t("ApiKey not valid!"),
      }
    );
    await new Promise((r) => setTimeout(r, 2000));
    if (success === true && data === true) location.reload(true);
  };

  const handleVerify = () => {
    const key = apikeyRef.current.value;
    if (key.length < 10) {
      toast.error(t("Please insert a valid ApiKey"));
      return;
    }
    apikeySubmit(key);
  };

  return (
    <div id="mainstage">
      <div className="flex">
        <div className="w-full">
          {started ? (
            <div className="apikey-insert">
              <div className="min-h-full flex items-center justify-center rounded">
                <div className="max-w-md w-full space-y-8 bg">
                  <div>
                    <img
                      className="mx-auto h-12 w-auto"
                      src={accelasearch_public_url + "as-logo.svg"}
                      alt="AccelaSearch Logo"
                    />
                    <h2 className="mt-6 text-center text-3xl font-extrabold text-gray-900">
                      {t("Link your account")}
                    </h2>
                    <p className="mt-2 text-center text-sm text-gray-600">
                      {t(
                        "Copy your Api Key from AccelaSearch console and paste it below"
                      )}
                    </p>
                  </div>
                  <form className="mt-8 space-y-6" method="POST">
                    <div className="rounded-md shadow-sm -space-y-px">
                      <div>
                        <label htmlFor="email-address" className="sr-only">
                          {t("ApiKey")}
                        </label>
                        <input
                          id="apikey"
                          name="apikey"
                          type="text"
                          ref={apikeyRef}
                          required
                          className="appearance-none rounded relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-as-primary-400 focus:border-as-primary-400 focus:z-10 sm:text-sm"
                          placeholder="Api Key"
                        />
                      </div>
                    </div>
                    <div>
                      <button
                        type="button"
                        className={cx(
                          "as-btn-primary",
                          isVerifying && "cursor-not-allowed"
                        )}
                        onClick={handleVerify}
                        disabled={isVerifying}
                      >
                        <span className="text-[14px]">
                          {t("Link to Accelasearch")}
                        </span>
                      </button>
                    </div>
                    <div className="mt-6">
                      <p className="text-center">
                        <a
                          href="https://console.accelasearch.io/signup"
                          target="_blank"
                          rel="noreferrer"
                          className="text-as-primary-400"
                        >
                          {t(
                            "Do you need an ApiKey? Try AccelaSearch for free for 30 days"
                          )}
                        </a>
                      </p>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          ) : (
            <div className="intro relative overflow-hidden">
              <div className="absolute top-[-1123px] right-[-10px] w-[780px] rotate-[-30deg] opacity-0 sm:opacity-100 invisible sm:visible">
                <img
                  src={accelasearch_public_url + "as_sfondo.svg"}
                  alt="AccelaSearch"
                />
              </div>
              <div>
                <img
                  src={accelasearch_public_url + "as-logo.svg"}
                  width="224"
                  height="61"
                  alt="AccelaSearch"
                />
              </div>
              <div className="mt-3">
                <p className="text-xl text-gray-800 font-bold">
                  {t("Boost your search engine without knowing one line code")}
                </p>
              </div>
              <div className="mt-6">
                <button
                  type="button"
                  className="as-btn-primary start_now w-auto"
                  onClick={() => setStarted(true)}
                >
                  <span className="text-[14px]">{t("Start now!")}</span>
                </button>
              </div>
              <div className="mt-4">
                <div className="text-xs text-gray-600">
                  <ul className="mb-4">
                    <li className="inline pl-3 pr-4 assure_checkbox">
                      {t("No credit card required")}
                    </li>
                    <li className="inline pl-3 pr-4 assure_checkbox">
                      {t("No coding skill required")}
                    </li>
                    <li className="inline pl-3 pr-4 assure_checkbox">
                      {t("Easy to configure")}
                    </li>
                  </ul>
                </div>
              </div>
              <div className="relative my-4 min-h-[30px]">
                <div
                  className="absolute inset-0 flex items-center"
                  aria-hidden="true"
                >
                  <div className="w-full border-t border-gray-300"></div>
                </div>
              </div>
              <div>
                <div className="sm:flex sm:flex-wrap px-12 my-12">
                  <div className="sm:w-[25%]">
                    <img
                      src={accelasearch_public_url + "relevant-search.svg"}
                    />
                  </div>
                  <div className="pl-12 sm:w-[75%] flex">
                    <div className="m-auto justify-center items-center">
                      <p className="text-xl text-gray-700 font-bold">
                        {t(
                          "AI Search Engine to show search results never seen before"
                        )}
                      </p>
                      <p className="text-lg text-gray-600">
                        {t(
                          "Giving your users the ability to find what they are looking for in a much simpler and AI-powered way means increasing the value of your products through faster and more relevant searches and results."
                        )}
                      </p>
                    </div>
                  </div>
                </div>
                <div className="sm:flex sm:flex-wrap px-12 my-12">
                  <div className="sm:w-[25%]">
                    <img src={accelasearch_public_url + "insights.svg"} />
                  </div>
                  <div className="pl-12 sm:w-[75%] flex">
                    <div className="m-auto justify-center items-center">
                      <p className="text-xl text-gray-700 font-bold">
                        {t(
                          "Collect valuable information from your users every day"
                        )}
                      </p>
                      <p className="text-lg text-gray-600">
                        {t(
                          "Learn from your users' behavior. Get to know their most searched and clicked products in a chosen time period. Learn more about your products and get all the information you were missing."
                        )}
                      </p>
                    </div>
                  </div>
                </div>
                <div className="sm:flex sm:flex-wrap px-12 my-12">
                  <div className="sm:w-[25%]">
                    <img src={accelasearch_public_url + "visual-ui.png"} />
                  </div>
                  <div className="pl-12 sm:w-[75%] flex">
                    <div className="m-auto justify-center items-center">
                      <p className="text-xl text-gray-700 font-bold">
                        {t("Create visual experiences without the use of code")}
                      </p>
                      <p className="text-lg text-gray-600">
                        {t(
                          "The No-code revolution has taken over and customizing your tools is more important than ever. AccelaSearch allows you to customize your search engine as you wish without relying on developers."
                        )}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
              <div className="relative my-4 min-h-[30px]">
                <div
                  className="absolute inset-0 flex items-center"
                  aria-hidden="true"
                >
                  <div className="w-full border-t border-gray-300"></div>
                </div>
              </div>
              <div className="mt-4 text-center">
                <p className="text-sm text-gray-600">
                  {t("Start using AccelaSearch now!")}
                </p>
                <div className="mt-4">
                  <button
                    type="button"
                    className="as-btn-primary start_now w-auto"
                    onClick={() => setStarted(true)}
                  >
                    <span className="text-[14px]">{t("Start now!")}</span>
                  </button>
                </div>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
