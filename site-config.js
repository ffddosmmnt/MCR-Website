"use strict";

// Public configuration only. Never put database credentials or tokens here.
// Empty = use api/ on the same host (Laragon/PHP).
// GitHub Pages requires an HTTPS backend, e.g. https://your-host.example/project/api/
window.MCR_CONFIG = Object.freeze({ apiBase: "" });

function resolveMcrApiBase() {
  const configured = window.MCR_CONFIG?.apiBase ?? "";
  if (!configured && location.hostname.endsWith(".github.io")) throw Object.assign(new Error(), { code: "api_not_configured" });
  const base = new URL(configured || "api/", document.baseURI);
  if (!["http:", "https:"].includes(base.protocol) || base.username || base.password || base.search || base.hash
      || (location.protocol === "https:" && base.protocol !== "https:")) throw Object.assign(new Error(), { code: "api_not_configured" });
  if (!base.pathname.endsWith("/")) base.pathname += "/";
  return base;
}
