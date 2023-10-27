export const cx = (...classes) => {
  return classes.filter(Boolean).join(" ");
};