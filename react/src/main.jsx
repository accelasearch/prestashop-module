import React from "react";
import ReactDOM from "react-dom/client";
import App from "./App.jsx";
import "./index.css";
import { store } from "./app/store";
import { Provider } from "react-redux";

ReactDOM.createRoot(document.getElementById("accelasearch-app")).render(
  <React.StrictMode>
    <Provider store={store}>
      <App />
    </Provider>
  </React.StrictMode>
);
