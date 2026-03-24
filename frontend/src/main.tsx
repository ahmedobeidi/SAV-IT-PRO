import React from "react";
import ReactDOM from "react-dom/client";
import { BrowserRouter } from "react-router-dom";
import App from "./app/App";

import "./app/styles/theme.css";
import "./app/styles/layout.css";
import "./app/styles/pages.css";
import "./app/styles/components.css";
import "./app/styles/utilities.css";

ReactDOM.createRoot(document.getElementById("root")!).render(
  <React.StrictMode>
    <BrowserRouter>
      <App />
    </BrowserRouter>
  </React.StrictMode>
);
