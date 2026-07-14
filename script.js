"use strict";

const MOBILE_NAV_QUERY = "(max-width: 980px)";

function initializeNavigation() {
  const header = document.querySelector(".site-header");
  const navigation = header?.querySelector(".main-nav");

  if (!header || !navigation) return;

  navigation.id ||= "main-navigation";
  const toggle = document.createElement("button");
  toggle.className = "nav-toggle";
  toggle.type = "button";
  toggle.setAttribute("aria-controls", navigation.id);
  toggle.setAttribute("aria-expanded", "false");
  toggle.innerHTML = `
    <span class="nav-toggle-label">Menu</span>
    <span class="nav-toggle-icon" aria-hidden="true"><i></i><i></i><i></i></span>
  `;
  navigation.before(toggle);
  header.classList.add("nav-ready");

  const closeNavigation = () => {
    header.classList.remove("nav-open");
    toggle.setAttribute("aria-expanded", "false");
  };

  toggle.addEventListener("click", () => {
    const isOpen = header.classList.toggle("nav-open");
    toggle.setAttribute("aria-expanded", String(isOpen));
  });

  navigation.addEventListener("click", (event) => {
    if (event.target instanceof HTMLAnchorElement) closeNavigation();
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && header.classList.contains("nav-open")) {
      closeNavigation();
      toggle.focus();
    }
  });

  window.matchMedia(MOBILE_NAV_QUERY).addEventListener("change", closeNavigation);
}

function initializeLyricsFilter() {
  const form = document.querySelector(".search-form");
  const input = document.querySelector("#lyrics-search");
  const table = document.querySelector(".lyrics-table");

  if (!(form instanceof HTMLFormElement) || !(input instanceof HTMLInputElement) || !table) return;

  const rows = [...table.querySelectorAll(".lyrics-row:not(.header)")];
  const status = document.createElement("p");
  status.className = "lyrics-status";
  status.setAttribute("role", "status");
  status.setAttribute("aria-live", "polite");
  form.after(status);

  const emptyState = document.createElement("p");
  emptyState.className = "lyrics-empty";
  emptyState.textContent = "No songs match your search.";
  emptyState.hidden = true;
  table.after(emptyState);

  const filterRows = () => {
    const query = input.value.trim().toLocaleLowerCase();
    let visibleCount = 0;

    for (const row of rows) {
      const matches = row.textContent?.toLocaleLowerCase().includes(query) ?? false;
      row.hidden = !matches;
      if (matches) visibleCount += 1;
    }

    status.textContent = query
      ? `${visibleCount} song${visibleCount === 1 ? "" : "s"} found for “${input.value.trim()}”.`
      : `Showing all ${rows.length} songs.`;
    emptyState.hidden = visibleCount !== 0;
  };

  form.addEventListener("submit", (event) => {
    event.preventDefault();
    filterRows();
  });
  input.addEventListener("input", filterRows);
  filterRows();
}

function initializeTicketSummary() {
  const quantityControl = document.querySelector(".quantity-control");
  const summary = document.querySelector(".summary-card");
  const deliveryInputs = [...document.querySelectorAll('input[name="delivery"]')];

  if (!quantityControl || !summary || deliveryInputs.length === 0) return;

  const buttons = [...quantityControl.querySelectorAll("button")];
  const output = quantityControl.querySelector("output");
  const itemName = summary.querySelector("[data-summary-item]");
  const itemPrice = summary.querySelector("[data-summary-price]");
  const servicePrice = summary.querySelector("[data-summary-service]");
  const deliveryName = summary.querySelector("[data-summary-delivery]");
  const deliveryPrice = summary.querySelector("[data-summary-delivery-price]");
  const totalPrice = summary.querySelector("[data-summary-total]");

  if (buttons.length !== 2 || !output || !itemName || !itemPrice || !servicePrice || !deliveryName || !deliveryPrice || !totalPrice) return;

  let quantity = Number(output.value || output.textContent) || 1;
  const formatPrice = (value) => `$${value.toFixed(2)}`;

  const renderSummary = () => {
    const selectedDelivery = deliveryInputs.find((input) => input instanceof HTMLInputElement && input.checked);
    const deliveryFee = selectedDelivery?.value === "will-call" ? 5 : 0;
    const ticketSubtotal = quantity * 125;
    const serviceFee = quantity * 21.25;
    const total = ticketSubtotal + serviceFee + 10 + deliveryFee;

    output.value = String(quantity);
    output.textContent = String(quantity);
    buttons[0].disabled = quantity === 1;
    buttons[1].disabled = quantity === 6;
    itemName.textContent = `General Admission x${quantity}`;
    itemPrice.textContent = formatPrice(ticketSubtotal);
    servicePrice.textContent = formatPrice(serviceFee);
    deliveryName.textContent = selectedDelivery?.value === "will-call" ? "Delivery Will Call" : "Delivery Mobile";
    deliveryPrice.textContent = deliveryFee ? formatPrice(deliveryFee) : "Free";
    totalPrice.textContent = formatPrice(total);

    for (const input of deliveryInputs) {
      input.closest(".delivery-option")?.classList.toggle("selected", input instanceof HTMLInputElement && input.checked);
    }
  };

  buttons[0].addEventListener("click", () => {
    quantity = Math.max(1, quantity - 1);
    renderSummary();
  });
  buttons[1].addEventListener("click", () => {
    quantity = Math.min(6, quantity + 1);
    renderSummary();
  });
  for (const input of deliveryInputs) input.addEventListener("change", renderSummary);

  renderSummary();
}

function initializeYouTubePlayer() {
  const player = document.querySelector("[data-youtube-player]");
  const localNotice = document.querySelector("[data-youtube-local-notice]");

  if (!(player instanceof HTMLIFrameElement) || !localNotice) return;

  if (location.protocol === "http:" || location.protocol === "https:") {
    player.src = player.dataset.src || "";
    return;
  }

  player.remove();
  localNotice.hidden = false;
}

initializeNavigation();
initializeLyricsFilter();
initializeTicketSummary();
initializeYouTubePlayer();
