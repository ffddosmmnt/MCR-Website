"use strict";

(async () => {
  const forms = [...document.querySelectorAll("[data-interaction-form]")];
  const feed = document.querySelector("[data-fan-messages]");
  if (!forms.length && !feed) return;
  let base, csrf;
  const hex = bytes => [...bytes].map(byte => byte.toString(16).padStart(2, "0")).join("");
  async function request(endpoint, body) {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 20000);
    try {
      const response = await fetch(new URL(endpoint, base), {
        method: body ? "POST" : "GET", credentials: "same-origin", signal: controller.signal,
        headers: { Accept: "application/json", ...(body ? { "Content-Type": "application/json" } : {}) },
        ...(body ? { body: JSON.stringify({ ...body, csrf }) } : {}),
      });
      const payload = await response.json();
      if (!response.ok) throw new Error(payload.error?.message || "The request failed.");
      return payload.data;
    } catch (error) {
      if (error.name === "AbortError") throw new Error("The response timed out. Please retry your submission.");
      throw error;
    } finally { clearTimeout(timeout); }
  }
  // Keep failure paths from falling through to a native form submission.
  forms.forEach(form => form.addEventListener("submit", event => event.preventDefault()));
  try { base = resolveMcrApiBase(); }
  catch {
    forms.forEach(form => { form.querySelector("[data-form-status]").textContent = "This form is temporarily unavailable. Please try again later."; });
    if (feed) feed.textContent = "Fan messages are currently unavailable.";
    return;
  }
  if (feed) {
    // Feed failures must not prevent visitors using the contact/subscription forms.
    request("fans.php").then(data => {
      feed.replaceChildren();
      if (!data.messages.length) { feed.textContent = "No fan messages have been published yet."; return; }
      for (const message of data.messages) {
        const card = document.createElement("figure");
        card.className = "fan-message";
        const quote = document.createElement("blockquote");
        quote.textContent = message.message;
        const author = document.createElement("figcaption");
        author.textContent = message.display_name;
        card.append(quote, author);
        feed.append(card);
      }
    }).catch(() => { feed.textContent = "Fan messages are currently unavailable. Please try again later."; });
  }
  if (base.origin !== location.origin) {
    for (const form of forms) {
      const link = document.createElement("a");
      link.className = "button";
      const page = form.dataset.interactionForm === "contact" ? "about" : document.body.dataset.page === "news-detail" ? "news-detail" : "news";
      const target = new URL(`../${page}.html`, base);
      if (page === "news-detail") target.searchParams.set("slug", new URLSearchParams(location.search).get("slug") || "");
      target.hash = form.dataset.interactionForm === "subscribe" ? "newsletter" : "contact-form";
      link.href = target.href;
      link.textContent = "Continue";
      form.querySelector("[data-form-status]").textContent = "Continue to complete this form.";
      form.append(link);
    }
    return;
  }
  try { ({ csrf } = await request("session.php")); }
  catch {
    forms.forEach(form => { form.querySelector("[data-form-status]").textContent = "This form is temporarily unavailable. Please reload to try again."; });
    return;
  }
  for (const form of forms) {
    const button = form.querySelector("button");
    const status = form.querySelector("[data-form-status]");
    const kind = form.dataset.interactionForm;
    let pending;
    button.disabled = false;
    status.textContent = "";
    form.addEventListener("submit", async () => {
      if (button.disabled || !form.reportValidity()) return;
      const values = Object.fromEntries(new FormData(form));
      const body = { ...values, kind, ...(kind === "subscribe" ? { consent: values.consent === "on" } : {}) };
      const controls = [...form.querySelectorAll("input,textarea,button")];
      controls.forEach(control => { control.disabled = true; });
      form.setAttribute("aria-busy", "true");
      status.textContent = "Submitting…";
      try {
        if (kind === "contact") {
          const serialized = JSON.stringify(body);
          const fingerprint = crypto.subtle ? hex(new Uint8Array(await crypto.subtle.digest("SHA-256", new TextEncoder().encode(serialized)))) : null;
          if (fingerprint) {
            try {
              const saved = JSON.parse(sessionStorage.getItem("mcr-contact-request"));
              if (saved?.hash === fingerprint && /^[a-f0-9]{64}$/.test(saved.key)) pending = { payload: serialized, key: saved.key };
            } catch { /* Memory-only retry remains available. */ }
          }
          if (!pending || pending.payload !== serialized) pending = { payload: serialized, key: hex(crypto.getRandomValues(new Uint8Array(32))) };
          if (fingerprint) {
            try { sessionStorage.setItem("mcr-contact-request", JSON.stringify({ hash: fingerprint, key: pending.key })); }
            catch { /* Never store message, email or name in browser storage. */ }
          }
          body.request_key = pending.key;
        }
        const data = await request("interaction.php", body);
        status.textContent = data.message;
        pending = null;
        if (kind === "contact") { try { sessionStorage.removeItem("mcr-contact-request"); } catch { /* Storage unavailable. */ } }
        form.reset();
      } catch (error) { status.textContent = error.message || "The service is temporarily unavailable. Please try again."; }
      finally {
        controls.forEach(control => { control.disabled = false; });
        form.removeAttribute("aria-busy");
      }
    });
  }
})();
