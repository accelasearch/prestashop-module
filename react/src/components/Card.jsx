import PropTypes from "prop-types";
export default function Card({
  Icon,
  title,
  description,
  ctaText = null,
  ctaHandler = null,
}) {
  return (
    <div className="p-6 bg-white border border-gray-200 rounded-lg shadow">
      <div className="w-8 h-8 text-gray-900">
        <Icon />
      </div>
      <h5 className="mb-2 text-2xl font-semibold tracking-tight text-gray-900 ">
        {title}
      </h5>
      <p className="mb-3 font-normal text-gray-500 ">{description}</p>
      {ctaText && (
        <button
          type="button"
          className="as-btn-primary bg-red-500"
          onClick={ctaHandler}
        >
          {ctaText}
        </button>
      )}
    </div>
  );
}

Card.propTypes = {
  Icon: PropTypes.func.isRequired,
  title: PropTypes.string.isRequired,
  description: PropTypes.string.isRequired,
  ctaText: PropTypes.string.isRequired,
  ctaHandler: PropTypes.func.isRequired,
};
