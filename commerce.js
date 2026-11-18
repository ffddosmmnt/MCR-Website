"use strict";

(async () => {
  const page = document.body.dataset.page;
  const status = document.querySelector("[data-shop-status]");
  let csrf = "";
  let base;
  const message = text => { status.textContent = text; };
  const showError = error => message(error.message || "The service is temporarily unavailable. Please try again.");
  async function request(endpoint, input) {
    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), 20000);
    try {
      const response = await fetch(new URL(endpoint, base), {
        method: input ? "POST" : "GET", credentials: "same-origin", signal: controller.signal,
        headers: input ? { "Content-Type": "application/json", Accept: "application/json" } : { Accept: "application/json" },
        ...(input ? { body: JSON.stringify({ ...input, csrf }) } : {}),
      });
      const payload = await response.json();
      if (!response.ok) throw Object.assign(new Error(payload.error?.message || "The request failed."), { status: response.status });
      return payload.data;
    } catch (error) {
      if (error.name === "AbortError") throw new Error("The response timed out. Retry the same order; do not create a second order.");
      throw error;
    } finally { clearTimeout(timer); }
  }
  const amount = value => formatRupiah(catalogCents(String(value)));
  function renderOrder(order) {
    const container = document.querySelector("[data-order-lines]");
    container.replaceChildren();
    for (const item of order.items) {
      const row = document.createElement("div");
      row.className = "shop-line";
      const label = document.createElement("span");
      const variant = [item.size, item.color].filter(Boolean).join(" / ");
      label.textContent = `${item.name}${variant ? " / " + variant : ""} × ${item.quantity}`;
      const price = document.createElement("strong");
      price.textContent = amount(item.subtotal);
      row.append(label, price);
      container.append(row);
    }
    document.querySelector("[data-order-total]").textContent = amount(order.total_amount);
    document.querySelector("[data-order-address]").textContent = order.address || order.items[0]?.address || "";
    document.querySelector("[data-quote-panel]").hidden = false;
  }
  try {
    base = resolveMcrApiBase();
    if (base.origin !== location.origin) {
      const link = document.querySelector("[data-backend-link]");
      const destination = new URL(`../${page}.html`, base);
      destination.search = location.search;
      link.href = destination.href;
      link.hidden = false;
      message("Continue to complete your order.");
      return;
    }
    const session = await request("session.php");
    csrf = session.csrf;
    const params = new URLSearchParams(location.search);
    if (page === "account") {
      const forms = document.querySelector("[data-auth-forms]");
      const account = document.querySelector("[data-account-user]");
      const renderAccount = user => {
        forms.hidden = !!user;
        account.hidden = !user;
        document.querySelector("[data-account-name]").textContent = user ? `${user.name} · ${user.email}` : "";
        document.querySelector("[data-account-role]").textContent = user ? (user.role === "admin" ? "Administrator" : "User") : "";
        document.querySelector("[data-admin-link]").hidden = user?.role !== "admin";
        for (const button of forms.querySelectorAll("button")) button.disabled = false;
        message(user ? "Your fan account is signed in." : "Sign in or create an account to place your order.");
      };
      const returnPath = params.get("return");
      if (returnPath) {
        try {
          const destination = new URL(returnPath, location.href);
          const checkout = new URL("../ticket-transaction.html", base);
          if (destination.origin === checkout.origin && destination.pathname === checkout.pathname
              && !destination.username && !destination.password) {
            document.querySelector("[data-return-link]").href = destination.href;
            document.querySelector("[data-return-link]").textContent = "Continue checkout";
          }
        } catch { /* Ignore malformed return links; account access remains available. */ }
      }
      renderAccount(session.user);
      for (const form of document.querySelectorAll("[data-account-form]")) {
        form.addEventListener("submit", async event => {
          event.preventDefault();
          const button = form.querySelector("button");
          button.disabled = true;
          message("Checking your account…");
          try {
            const data = await request("session.php", { ...Object.fromEntries(new FormData(form)), action: form.dataset.accountForm });
            csrf = data.csrf;
            form.reset();
            renderAccount(data.user);
          } catch (error) { showError(error); }
          finally { button.disabled = false; }
        });
      }
      document.querySelector("[data-logout]").addEventListener("click", async () => {
        try { const data = await request("session.php", { action: "logout" }); csrf = data.csrf; renderAccount(null); }
        catch (error) { showError(error); }
      });
      return;
    }
    if (!session.user) {
      const link = document.querySelector("[data-sign-in]");
      link.href = `account.html?return=${encodeURIComponent("ticket-transaction.html" + location.search)}`;
      link.hidden = false;
      message("Sign in before reviewing or creating an order.");
      return;
    }
    const kind = params.get("kind");
    if (!["ticket", "merch"].includes(kind)) throw new Error("Choose tickets from Tour or merchandise from Store first.");
    if (params.has("order")) {
      const data = await request(`checkout.php?kind=${kind}&id=${encodeURIComponent(params.get("order"))}`);
      renderOrder(data.order);
      document.querySelector("[data-order-title]").textContent = `Order #${data.order.id} · ${data.order.status}`;
      document.querySelector("[data-new-order]").hidden = false;
      message("Your order details are shown below.");
      return;
    }
    const panel = document.querySelector("[data-checkout-panel]");
    const form = document.querySelector("[data-checkout-form]");
    const review = document.querySelector("[data-review-order]");
    const create = document.querySelector("[data-create-order]");
    document.querySelector("[data-account-name]").textContent = `Ordering as ${session.user.name}`;
    let merchItems = [];
    if (kind === "ticket") {
      const data = await request(`tickets.php?concert=${encodeURIComponent(params.get("concert") || "")}`);
      const concert = data.concert;
      document.querySelector("[data-concert-label]").textContent = `${concert.venue_name} · ${concert.city} · ${concert.date_display} · ${concert.status}`;
      document.querySelector("[data-ticket-fields]").hidden = false;
      const select = form.elements.ticket_type;
      for (const ticket of data.tickets) {
        const option = document.createElement("option");
        option.value = ticket.id;
        option.textContent = `${ticket.name} · ${amount(ticket.price)} · ${ticket.quota} remaining`;
        option.disabled = ticket.quota === 0;
        select.append(option);
      }
      const available = [...select.options].find(option => !option.disabled);
      if (!available) throw new Error("No tickets are available for this concert.");
      select.value = available.value;
      select.disabled = false;
      form.elements.quantity.disabled = false;
    } else {
      const raw = params.getAll("item");
      if (!raw.length || raw.length > 20 || raw.some(item => !/^[1-9][0-9]{0,9}:[1-9][0-9]{0,9}$/.test(item))) throw new Error("Return to Store and select merchandise variants.");
      merchItems = raw.map(item => { const [id, quantity] = item.split(":").map(Number); return { id, quantity }; });
      document.querySelector("[data-merch-selection]").textContent = `${merchItems.length} merchandise variant(s) selected. Review to load current names and prices.`;
      document.querySelector("[data-merch-selection]").hidden = false;
      document.querySelector("[data-address-field]").hidden = false;
      form.elements.address.disabled = false;
      form.elements.address.required = true;
    }
    panel.hidden = false;
    review.disabled = false;
    message("Review your selection before placing your order.");
    let selection;
    let quote;
    let key;
    let storageKey;
    let submitted = false;
    form.addEventListener("input", () => { if (!submitted) create.hidden = true; });
    form.addEventListener("submit", async event => {
      event.preventDefault();
      if (submitted) return;
      review.disabled = true;
      create.hidden = true;
      selection = { kind, items: kind === "ticket" ? [{ id: Number(form.elements.ticket_type.value), quantity: Number(form.elements.quantity.value) }] : merchItems, address: kind === "merch" ? form.elements.address.value.trim() : "" };
      const fieldStates = [...form.querySelectorAll("input,select,textarea")].map(field => [field, field.disabled]);
      for (const [field] of fieldStates) field.disabled = true;
      try {
        const data = await request("checkout.php", { ...selection, action: "quote" });
        quote = data.quote;
        storageKey = "mcr-request:" + JSON.stringify({ kind, items: selection.items });
        try { key = sessionStorage.getItem(storageKey); } catch { /* Memory-only fallback. */ }
        if (!key) key = Array.from(crypto.getRandomValues(new Uint8Array(32)), byte => byte.toString(16).padStart(2, "0")).join("");
        try { sessionStorage.setItem(storageKey, key); } catch { /* Key still survives in this page. */ }
        renderOrder(quote);
        create.hidden = false;
        message("Review your items and total, then place your order.");
      } catch (error) { showError(error); }
      finally {
        review.disabled = false;
        for (const [field, disabled] of fieldStates) field.disabled = disabled;
      }
    });
    create.addEventListener("click", async () => {
      if (!selection || !quote) return;
      create.disabled = true;
      submitted = true;
      for (const input of form.querySelectorAll("input,textarea,select,button")) input.disabled = true;
      message("Placing your order…");
      try {
        const data = await request("checkout.php", { ...selection, action: "create", request_key: key, quote_hash: quote.quote_hash });
        renderOrder(data.order);
        create.hidden = true;
        try { sessionStorage.removeItem(storageKey); } catch { /* No stored key. */ }
        history.replaceState(null, "", `ticket-transaction.html?kind=${kind}&order=${data.order.id}`);
        document.querySelector("[data-order-title]").textContent = `Order #${data.order.id} · ${data.order.status}`;
        document.querySelector("[data-new-order]").hidden = false;
        message(data.replayed ? "Your existing order was recovered; no duplicate was created." : "Your order has been placed and is pending.");
      } catch (error) {
        showError(error);
        if (error.status >= 400 && error.status < 500) {
          submitted = false;
          review.disabled = false;
          create.hidden = true;
          for (const input of form.querySelectorAll(kind === "ticket" ? "[data-ticket-fields] input,[data-ticket-fields] select" : "textarea")) input.disabled = false;
        } else create.textContent = "Retry the same order";
      }
      finally { create.disabled = false; }
    });
  } catch (error) {
    if (error.code === "api_not_configured") message("Checkout is currently unavailable. Please try again later.");
    else showError(error);
  }
})();
