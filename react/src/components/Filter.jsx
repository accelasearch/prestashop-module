import { t } from "../utils";
import PropTypes from "prop-types";

const FilterComponent = ({
  filterText,
  onFilter,
  onClear,
  filterPlaceholder = t("Cerca per sku, nome o brand..."),
  showClear = true,
}) => (
  <form className="mb-8 w-full" onSubmit={(e) => e.preventDefault()}>
    <div className="relative">
      <div className="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
        <svg
          aria-hidden="true"
          className="w-5 h-5 text-gray-500"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            strokeLinecap="round"
            strokeLinejoin="round"
            strokeWidth="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
          ></path>
        </svg>
      </div>
      <input
        id="search_form"
        type="search"
        value={filterText}
        onChange={onFilter}
        className="block p-6 pl-10 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500"
        placeholder={filterPlaceholder}
      />
      {showClear && (
        <button
          type="submit"
          className="text-white absolute right-2.5 bottom-2 bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2"
          onClick={onClear}
        >
          {t("Pulisci")}
        </button>
      )}
    </div>
  </form>
);

FilterComponent.propTypes = {
  filterText: PropTypes.string.isRequired,
  onFilter: PropTypes.func.isRequired,
  onClear: PropTypes.func.isRequired,
  filterPlaceholder: PropTypes.string,
  showClear: PropTypes.bool,
};

export default FilterComponent;
