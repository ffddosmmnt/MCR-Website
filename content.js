"use strict";

// HTML owns presentation; PHP endpoints own database access. All API text is
// inserted as textContent, never interpreted as HTML.
(async () => {
  const page = document.body.dataset.page;
  const endpoints = { index: "home.php", music: "music.php", news: "news.php", "news-detail": "article.php", tour: "tour.php" };
  if (!endpoints[page]) return;
  const status = document.querySelector("[data-content-status]");
  const find = (root, selector) => root.querySelector(selector);
  const text = (root, selector, value) => root.querySelectorAll(selector).forEach(node => { node.textContent = value ?? ""; });
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
  const fillNews = (root, item) => {
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
    const configured = window.MCR_CONFIG?.apiBase ?? "";
    if (!configured && location.hostname.endsWith(".github.io")) {
      throw Object.assign(new Error(), { code: "api_not_configured" });
    }
    const apiBase = new URL(configured || "api/", document.baseURI);
    if (!["http:", "https:"].includes(apiBase.protocol) || apiBase.username || apiBase.password
        || apiBase.search || apiBase.hash || (location.protocol === "https:" && apiBase.protocol !== "https:")) {
      throw Object.assign(new Error(), { code: "api_not_configured" });
    }
    if (!apiBase.pathname.endsWith("/")) apiBase.pathname += "/";
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

    if (page === "index") {
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
      data.songs.forEach(song => {
        clone("song-row-template", songList, root => {
          text(root, "[data-title]", song.title);
          text(root, "[data-album]", song.album_title);
          find(root, "[data-song-link]").href = `#song-${song.id}`;
          find(root, ".lyrics-row").dataset.searchText = `${song.title} ${song.album_title} ${song.lyrics ?? ""}`;
        });
        clone("playlist-template", playlist, root => {
          find(root, "details").id = `song-${song.id}`;
          text(root, "[data-title]", song.title);
          text(root, "[data-album]", `/ ${song.album_title}`);
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
      text(shell, "[data-body]", data.article.content || "The article content is not available yet.");
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
      if (data.selected) {
        const selected = find(document, "[data-selected-concert]");
        fillConcert(selected, data.selected);
        selected.hidden = false;
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
      api_not_configured: "This static preview is available, but live content has not been connected yet.",
      invalid_request: "The page request is invalid. Please check the search text or content link.",
      not_found: "The requested article or concert was not found. Please return to the news or tour list.",
    };
    status.textContent = messages[error.code] ?? "Live content is temporarily unavailable. Please try again later.";
    status.setAttribute("role", "alert");
    status.hidden = false;
    if (page === "news-detail") text(document, "[data-page-heading]", error.code === "not_found" ? "Article not found" : "News unavailable");
  }
})();
