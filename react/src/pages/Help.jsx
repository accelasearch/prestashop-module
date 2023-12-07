import { Disclosure } from "@headlessui/react";
import { t } from "../utils";
import {
  CheckIcon,
  MinusSmallIcon,
  PlusSmallIcon,
} from "@heroicons/react/20/solid";
import { faqs } from "../faqs";

export default function Help() {
  return (
    <div>
      <div className="">
        <div className="flex flex-wrap">
          <div className="w-full sm:w-1/2 flex">
            <div>
              <p className="">
                <span className="text-xl font-extrabold text-gray-900 sm:text-2xl">
                  {t("What can you do with this module?")}
                </span>
                <ul className="pl-4 pt-4">
                  <li className="pb-1 flex items-center gap-2">
                    <span className="w-6 h-6 text-green-600">
                      <CheckIcon />
                    </span>
                    <span>
                      {t(
                        "Relevant Search Results: Accelasearch delivers highly relevant search results"
                      )}
                    </span>
                  </li>
                  <li className="pb-1 flex items-center gap-2">
                    <span className="w-6 h-6 text-green-600">
                      <CheckIcon />
                    </span>
                    <span>
                      {t(
                        "Enhanced User Experience: Advanced search features make online shopping more efficient."
                      )}
                    </span>
                  </li>
                  <li className="pb-1 flex items-center gap-2">
                    <span className="w-6 h-6 text-green-600">
                      <CheckIcon />
                    </span>
                    <span>
                      {t(
                        "Increased Sales: Accurate results and personalized recommendations can boost sales."
                      )}
                    </span>
                  </li>
                  <li className="pb-1 flex items-center gap-2">
                    <span className="w-6 h-6 text-green-600">
                      <CheckIcon />
                    </span>
                    <span>
                      {t(
                        "Customization: Businesses can tailor the search experience to their brand and products."
                      )}
                    </span>
                  </li>
                </ul>
              </p>
            </div>
          </div>
          <div className="w-full sm:w-1/2 px-4">
            <div className="bg-gray-100 p-8 text-center">
              <p className="text-center text-zinc-600 pb-4 text-xl">
                {t("Cannot find a solution to your issue?")}
              </p>
              <a
                href="https://dgcalsupport.atlassian.net/servicedesk/customer/portal/3"
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-as-primary-500 hover:bg-as-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-as-primary-500"
              >
                {t("Open a support ticket")}
              </a>
            </div>
          </div>
        </div>
        <div className="">
          <div className="">
            <div className="divide-y divide-gray-900/10">
              <h2 className="text-2xl font-bold leading-10 tracking-tight text-gray-900">
                {t("Frequently asked questions")}
              </h2>
              <dl className="mt-10 space-y-6 divide-y divide-gray-900/10">
                {faqs.map((faq) => (
                  <Disclosure as="div" key={faq.question} className="pt-6">
                    {({ open }) => (
                      <>
                        <dt>
                          <Disclosure.Button className="flex w-full items-start justify-between text-left text-gray-900">
                            <span className="text-base font-semibold leading-7">
                              {faq.question}
                            </span>
                            <span className="ml-6 flex h-7 items-center">
                              {open ? (
                                <MinusSmallIcon
                                  className="h-6 w-6"
                                  aria-hidden="true"
                                />
                              ) : (
                                <PlusSmallIcon
                                  className="h-6 w-6"
                                  aria-hidden="true"
                                />
                              )}
                            </span>
                          </Disclosure.Button>
                        </dt>
                        <Disclosure.Panel as="dd" className="mt-2 pr-12">
                          <p className="text-base leading-7 text-gray-600">
                            {faq.answer}
                          </p>
                        </Disclosure.Panel>
                      </>
                    )}
                  </Disclosure>
                ))}
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
