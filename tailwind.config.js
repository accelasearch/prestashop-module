/** @type {import('tailwindcss').Config} */
module.exports = {
  important: "#accelasearch-app",
  content: [
    "./views/templates/admin/*.{html,js,tpl}",
    "./views/**/*.{html,js,tpl}"
  ],
  theme: {
    extend : {
      colors : {
        "as-primary" : {
          400 : "#398ff4",
          700 : "#3078cc"
        }
      }
    }
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
