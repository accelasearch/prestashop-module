export const cx = (...classes) => {
  return classes.filter(Boolean).join(" ");
};

/**
 * Translator helper function
 *
 * @param {string} k
 */
export const t = (k) => {
  return _AST[k] || k;
};
