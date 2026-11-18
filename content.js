"use strict";

// HTML owns presentation; PHP endpoints own database access. All API text is
// inserted as textContent, never interpreted as HTML.
(async () => {
  const page = document.body.dataset.page;
  const endpoints = { index: "home.php", music: "music.php", news: "news.php", "news-detail": "article.php", tour: "tour.php", store: "catalog.php" };
  if (!endpoints[page]) return;
  const status = document.querySelector("[data-content-status]");
  const find = (root, selector) => root.querySelector(selector);
  const text = (root, selector, value) => root.querySelectorAll(selector).forEach(node => { node.textContent = value ?? ""; });
  const articleBody = (root, value) => {
    // Sources remain plain database text; build links with DOM APIs, never HTML.
    const body = find(root, "[data-body]");
    const content = value || "The article content is not available yet.";
    body.replaceChildren();
    let offset = 0;
    for (const match of content.matchAll(/https:\/\/[^\s<>"']+/g)) {
      body.append(document.createTextNode(content.slice(offset, match.index)));
      const link = document.createElement("a");
      link.href = match[0];
      link.textContent = match[0];
      link.target = "_blank";
      link.rel = "noopener noreferrer";
      body.append(link);
      offset = match.index + match[0].length;
    }
    body.append(document.createTextNode(content.slice(offset)));
  };
  const notice = (container, message) => {
    const node = document.createElement("p");
    node.className = "content-notice";
    node.setAttribute("role", "status");
    node.textContent = message;
    container.append(node);
  };
  const clone = (templateId, container, fill) => {
    const fragment = document.getElementById(templateId).content.cloneNode(true);
    fill(fragment);
    container.append(fragment);
  };
  const articleLink = item => `news-detail.html?slug=${encodeURIComponent(item.slug)}`;
  const concertLink = item => `tour.html?concert=${encodeURIComponent(item.id)}#selected-show`;
  const fillNewsImage = (root, item) => {
    root.querySelectorAll("[data-news-image]").forEach(image => {
      image.src = new URL(item.image_path || "assets/archive-news.png", new URL("../", resolveMcrApiBase())).href;
      image.alt = item.image_path ? item.title : "Editorial archive artwork";
    });
    root.querySelectorAll("[data-news-image-caption]").forEach(caption => { caption.hidden = Boolean(item.image_path); });
  };
  const fillNews = (root, item) => {
    fillNewsImage(root, item);
    text(root, "[data-title]", item.title);
    text(root, "[data-date]", item.date_display);
    text(root, "[data-excerpt]", item.excerpt);
    root.querySelectorAll("[data-article-link]").forEach(link => { link.href = articleLink(item); });
  };
  const fillConcert = (root, item) => {
    text(root, "[data-date]", item.date_display);
    text(root, "[data-city]", `${item.city}, ${item.country}`);
    text(root, "[data-venue]", item.venue_name);
    text(root, "[data-status]", item.status);
    text(root, "[data-id]", `#${item.id}`);
    root.querySelectorAll("[data-concert-link]").forEach(link => { link.href = concertLink(item); });
  };

  try {
    const apiBase = resolveMcrApiBase();
    const url = new URL(endpoints[page], apiBase);
    if (["music", "news-detail", "tour"].includes(page)) url.search = location.search;
    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), 10000);
    let response;
    let payload;
    try {
      response = await fetch(url, { headers: { Accept: "application/json" }, credentials: "omit", signal: controller.signal });
      if (!response.headers.get("Content-Type")?.includes("application/json")) throw new Error("Invalid API response");
      payload = await response.json();
    } finally {
      clearTimeout(timer);
    }
    if (!response.ok) throw Object.assign(new Error(), { code: payload.error?.code });
    const data = payload.data;
    if (!data || typeof data !== "object") throw new Error("Invalid content response");

    if (page === "store") {
      const filter = find(document, "[data-store-filter]");
      const grid = find(document, "[data-product-grid]");
      const shownCategories = new Set(data.products.map(product => product.category_id));
      data.categories.filter(category => shownCategories.has(category.id)).forEach(category => {
        const option = document.createElement("option");
        option.value = String(category.id);
        option.textContent = category.name;
        filter.append(option);
      });
      data.products.forEach(product => clone("product-template", grid, root => {
        const card = find(root, ".product-card");
        card.dataset.category = String(product.category_id);
        card.dataset.name = product.name;
        card.dataset.price = product.price;
        if (product.image_path) {
          const image = find(root, "[data-product-image]");
          image.src = new URL(product.image_path, new URL("../", apiBase)).href;
          image.alt = product.name;
          find(root, "[data-product-image-frame]").hidden = false;
        }
        text(root, "[data-product-name]", product.name);
        text(root, "[data-product-details]", product.details || "No description available.");
        text(root, "[data-category-name]", product.category_name);
        text(root, "[data-product-price]", formatRupiah(catalogCents(product.price)));
        const select = find(root, "[data-variant-select]");
        select.setAttribute("aria-label", `Variant for ${product.name}`);
        product.variants.forEach(variant => {
          const option = document.createElement("option");
          option.value = String(variant.id);
          option.dataset.stock = String(variant.stock);
          option.dataset.label = [variant.size, variant.color].filter(Boolean).join(" / ") || `Variant #${variant.id}`;
          option.textContent = `${option.dataset.label} · ${variant.stock} in stock`;
          option.disabled = variant.stock === 0;
          select.append(option);
        });
        if (!product.variants.length) {
          const option = document.createElement("option");
          option.textContent = "No variants available";
          select.append(option);
        }
        const available = [...select.options].find(option => !option.disabled && option.value && option.dataset.stock);
        if (available) select.value = available.value;
        const inStock = product.variants.some(variant => variant.stock > 0);
        select.disabled = !inStock;
        find(root, "[data-add-to-bag]").disabled = !inStock;
        text(root, "[data-variant-stock]", inStock ? "Availability shown per variant; stock is not reserved." : "Currently unavailable.");
      }));
      filter.disabled = false;
      find(document, "[data-store-empty]").hidden = data.products.length !== 0;
    } else if (page === "index") {
      const shows = find(document, "[data-home-concerts]");
      const news = find(document, "[data-home-news]");
      if (!data.concerts.length) notice(shows, "No tour dates have been published yet.");
      data.concerts.forEach(item => clone("home-concert-template", shows, root => fillConcert(root, item)));
      if (!data.news.length) notice(news, "No news has been published yet.");
      data.news.forEach(item => clone("home-news-template", news, root => fillNews(root, item)));
    } else if (page === "music") {
      const fillAlbum = (root, item) => {
        text(root, "[data-title]", item.title);
        text(root, "[data-date]", item.date_display);
        const image = find(root, "[data-cover]");
        // Backend resolver permits only existing public asset/upload paths.
        image.src = new URL("../" + item.cover_image, apiBase).href;
        image.alt = `Artwork for ${item.title}`;
      };
      const albumList = find(document, "[data-albums]");
      if (!data.albums.length) notice(albumList, "No albums have been published yet.");
      if (data.albums.length) {
        const featured = find(document, "[data-featured-album]");
        fillAlbum(featured, data.albums[0]);
        featured.hidden = false;
      }
      data.albums.forEach(item => clone("album-template", albumList, root => fillAlbum(root, item)));
      const songList = find(document, "[data-songs]");
      const playlist = find(document, "[data-playlist]");
      const fillSongCover = (root, song) => {
        find(root, "[data-song-cover]").src = new URL("../" + song.album_cover, apiBase).href;
      };
      data.songs.forEach(song => {
        clone("song-row-template", songList, root => {
          fillSongCover(root, song);
          text(root, "[data-title]", song.title);
          text(root, "[data-album]", song.album_title);
          find(root, "[data-song-link]").href = `#song-${song.id}`;
          find(root, ".lyrics-row").dataset.searchText = `${song.title} ${song.album_title} ${song.lyrics ?? ""}`;
        });
        clone("playlist-template", playlist, root => {
          fillSongCover(root, song);
          find(root, "details").id = `song-${song.id}`;
          text(root, "[data-title]", song.title);
          text(root, "[data-album]", song.album_title);
          text(root, "[data-lyrics]", song.lyrics || "Lyrics are not available for this song yet.");
        });
      });
      if (!data.songs.length) notice(playlist, "The shared playlist is empty.");
      const input = document.getElementById("lyrics-search");
      input.value = data.query;
      input.disabled = false;
      document.querySelector(".search-form button").disabled = false;
    } else if (page === "news") {
      const list = find(document, "[data-news]");
      if (!data.articles.length) notice(list, "No news has been published yet.");
      if (data.articles.length) {
        const featured = find(document, "[data-featured-news]");
        fillNews(featured, data.articles[0]);
        featured.hidden = false;
      }
      data.articles.slice(1).forEach(item => clone("news-template", list, root => fillNews(root, item)));
    } else if (page === "news-detail") {
      const shell = find(document, "[data-article-shell]");
      text(shell, "[data-title]", data.article.title);
      text(shell, "[data-date]", data.article.date_display);
      articleBody(shell, data.article.content);
      fillNewsImage(shell, data.article);
      document.title = `${data.article.title} | MCR`;
      find(document, "[data-page-heading]").hidden = true;
      shell.hidden = false;
      const related = find(document, "[data-related]");
      if (!data.related.length) notice(related, "No other articles have been published yet.");
      data.related.forEach(item => clone("related-template", related, root => fillNews(root, item)));
    } else if (page === "tour") {
      const list = find(document, "[data-concerts]");
      data.concerts.forEach(item => clone("concert-template", list, root => fillConcert(root, item)));
      find(document, "[data-tour-empty]").hidden = data.concerts.length !== 0;
      const concertSelect = find(document, "#ticket-concert");
      data.concerts.forEach(concert => {
        const option = document.createElement("option");
        option.value = String(concert.id);
        option.textContent = `${concert.city} · ${concert.venue_name} · ${concert.date_display} · ${concert.status}`;
        concertSelect.append(option);
      });
      concertSelect.disabled = data.concerts.length === 0;
      find(document, ".ticket-concert-form button").disabled = concertSelect.disabled;
      const ticketStatus = find(document, "[data-ticket-status]");
      if (!data.concerts.length) ticketStatus.textContent = "No concerts are available to select.";
      if (data.selected) {
        const selected = find(document, "[data-selected-concert]");
        fillConcert(selected, data.selected);
        selected.hidden = false;
        concertSelect.value = String(data.selected.id);
        ticketStatus.textContent = !data.selected.bookable ? "Ticket sales are closed for this concert."
          : !data.tickets.length ? "No ticket types have been published for this concert."
          : "Choose a ticket below to continue. No tickets are reserved until your order is placed.";
        data.tickets.forEach(ticket => clone("tour-ticket-template", find(document, "[data-tour-tickets]"), root => {
          text(root, "[data-ticket-name]", ticket.name);
          text(root, "[data-ticket-price]", formatRupiah(catalogCents(ticket.price)));
          text(root, "[data-ticket-quota]", `${ticket.quota} tickets remaining`);
          if (data.selected.bookable && ticket.quota > 0) {
            const link = find(root, "[data-choose-ticket]");
            link.href = `ticket-transaction.html?kind=ticket&concert=${encodeURIComponent(data.selected.id)}&ticket=${encodeURIComponent(ticket.id)}`;
            link.hidden = false;
          } else {
            const unavailable = find(root, "[data-ticket-unavailable]");
            unavailable.textContent = data.selected.bookable ? "Sold out" : "Sales closed";
            unavailable.hidden = false;
          }
        }));
      }
    }
    status.hidden = true;
    document.body.dataset.contentState = "ready";
    document.dispatchEvent(new Event("mcr:content-ready"));
    const anchor = document.getElementById(location.hash.slice(1));
    if (anchor) requestAnimationFrame(() => anchor.scrollIntoView({ block: "start" }));
  } catch (error) {
    document.body.dataset.contentState = "error";
    const messages = {
      api_not_configured: "Content is currently unavailable. Please try again later.",
      invalid_request: "The page request is invalid. Please check the search text or content link.",
      not_found: "The requested article or concert was not found. Please return to the news or tour list.",
    };
    status.textContent = messages[error.code] ?? "Live content is temporarily unavailable. Please try again later.";
    status.setAttribute("role", "alert");
    status.hidden = false;
    if (page === "news-detail") text(document, "[data-page-heading]", error.code === "not_found" ? "Article not found" : "News unavailable");
  }
})();
