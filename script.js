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
  const status = document.querySelector("[data-lyrics-status]") ?? document.createElement("p");
  status.className = "lyrics-status";
  status.setAttribute("role", "status");
  status.setAttribute("aria-live", "polite");
  if (!status.isConnected) form.after(status);

  const emptyState = document.querySelector("[data-lyrics-empty]") ?? document.createElement("p");
  emptyState.className = "lyrics-empty";
  emptyState.textContent = "No songs match your search.";
  emptyState.hidden = true;
  if (!emptyState.isConnected) table.after(emptyState);

  const filterRows = () => {
    const query = input.value.trim().toLocaleLowerCase();
    let visibleCount = 0;

    for (const row of rows) {
      const searchText = row.dataset.searchText ?? row.textContent ?? "";
      const matches = searchText.toLocaleLowerCase().includes(query);
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

function initializeSongDetails() {
  if (!document.querySelector(".song-details")) return;

  const revealSong = (hash) => {
    if (!/^#song-\d+$/.test(hash)) return;
    const song = document.getElementById(hash.slice(1));
    if (song instanceof HTMLDetailsElement) song.open = true;
  };

  document.addEventListener("click", (event) => {
    const link = event.target instanceof Element ? event.target.closest('a[href^="#song-"]') : null;
    if (link instanceof HTMLAnchorElement) revealSong(link.hash);
  });
  window.addEventListener("hashchange", () => revealSong(location.hash));
  revealSong(location.hash);
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

function catalogCents(value) {
  if (!/^[0-9]{1,10}\.[0-9]{2}$/.test(value)) throw new Error("Invalid catalog price");
  return BigInt(value.replace(".", ""));
}

function formatRupiah(cents) {
  const amount = cents / 100n;
  return `Rp ${amount.toLocaleString("id-ID")},${String(cents % 100n).padStart(2, "0")}`;
}

function initializeMerchShowcase() {
  const filter = document.querySelector("[data-store-filter]");
  const productGrid = document.querySelector("[data-product-grid]");
  const emptyState = document.querySelector("[data-store-empty]");
  const bagItems = document.querySelector("[data-bag-items]");
  const bagTotal = document.querySelector("[data-bag-total]");
  const status = document.querySelector("[data-bag-status]");
  if (!filter || !productGrid || !emptyState || !bagItems || !bagTotal) return;
  const bag = new Map();
  const products = [...productGrid.querySelectorAll(".product-card")];
  filter.addEventListener("change", () => {
    let count = 0;
    for (const card of products) {
      card.hidden = filter.value !== "all" && card.dataset.category !== filter.value;
      if (!card.hidden) count++;
    }
    emptyState.hidden = count !== 0;
  });
  const renderBag = () => {
    bagItems.replaceChildren();
    let total = 0n;
    for (const [id, item] of bag) {
      total += item.cents * BigInt(item.quantity);
      const row = document.createElement("div");
      row.className = "bag-line";
      const name = document.createElement("span");
      name.textContent = `${item.name} / ${item.label} × ${item.quantity}`;
      const amount = document.createElement("strong");
      amount.textContent = formatRupiah(item.cents * BigInt(item.quantity));
      const remove = document.createElement("button");
      remove.type = "button";
      remove.className = "button secondary small";
      remove.textContent = "Remove";
      remove.setAttribute("aria-label", `Remove ${item.name} / ${item.label}`);
      remove.addEventListener("click", () => { bag.delete(id); renderBag(); });
      row.append(name, amount, remove);
      bagItems.append(row);
    }
    if (!bag.size) {
      const empty = document.createElement("p");
      empty.textContent = "Your bag is empty. Add a variant from the catalog.";
      bagItems.append(empty);
    }
    bagTotal.textContent = formatRupiah(total);
    const checkout = document.querySelector("[data-merch-checkout]");
    if (checkout) {
      checkout.hidden = !bag.size;
      const params = new URLSearchParams();
      params.set("kind", "merch");
      for (const [id, item] of bag) params.append("item", `${id}:${item.quantity}`);
      checkout.href = `ticket-transaction.html?${params}`;
    }
  };
  productGrid.addEventListener("click", event => {
    const button = event.target instanceof Element ? event.target.closest("[data-add-to-bag]") : null;
    const card = button?.closest(".product-card");
    if (!card || button.disabled) return;
    const option = card.querySelector("[data-variant-select]").selectedOptions[0];
    if (!option || option.disabled) return;
    const id = option.value;
    const current = bag.get(id) || { name: card.dataset.name, label: option.dataset.label, cents: catalogCents(card.dataset.price), quantity: 0 };
    if (current.quantity >= Number(option.dataset.stock)) {
      status.textContent = "The selected quantity has reached the displayed stock. Availability is checked again at checkout.";
      return;
    }
    if (bag.size >= 20 && !bag.has(id)) { status.textContent = "A bag can contain up to 20 variants."; return; }
    current.quantity++;
    bag.set(id, current);
    status.textContent = "Added to your temporary bag. No stock is reserved yet.";
    renderBag();
  });
  renderBag();
}

initializeNavigation();
if (document.body.dataset.page !== "music") {
  initializeLyricsFilter();
  initializeSongDetails();
}
document.addEventListener("mcr:content-ready", () => {
  if (document.body.dataset.page === "music") {
    initializeLyricsFilter();
    initializeSongDetails();
  }
  if (document.body.dataset.page === "store") initializeMerchShowcase();
}, { once: true });
initializeYouTubePlayer();
