/** @type {import('tailwindcss').Config} */

const forms = require("@tailwindcss/forms");

export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}",
    "./node_modules/react-tailwindcss-datepicker/dist/index.esm.js",
  ],
  theme: {
    extend: {
      colors: {
        "as-primary": {
          500: "#398ff4",
          600: "#3078cc",
        },
      },
    },
  },
  important: "#accelasearch-app",
  plugins: [forms],
};
