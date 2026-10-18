"use strict";
(() => {
  const nav = document.getElementById("public-nav");
  const toggle = document.querySelector(".nav-toggle");
  const wordmark = document.querySelector(".site-header .wordmark");
  if (!nav || !toggle || !wordmark) return;

  let activeController = null;

  const isInternalPublicUrl = url => {
    const home = new URL(wordmark.href, location.href);
    return !url.searchParams.has("sitemap")
      && url.origin === location.origin
      && url.pathname.startsWith(home.pathname)
      && !/\/(admin|install|account|payments)(\/|$)/.test(url.pathname)
      && !/\/shop\/(cart|checkout|order|pay)\.php$/.test(url.pathname)
      && !/\/media\/file\.php$/.test(url.pathname);
  };

  const closeMenu = () => {
    nav.classList.remove("is-open");
    nav.style.removeProperty("--menu-max-height");
    nav.style.removeProperty("height");
    nav.style.removeProperty("max-height");
    toggle.setAttribute("aria-expanded", "false");
  };

  const replaceHeadMetadata = nextDocument => {
    const selectors = [
      'meta[name="description"]',
      'meta[name="robots"]',
      'link[rel="canonical"]',
      'meta[property^="og:"]',
      'meta[name^="twitter:"]',
      'script#page-structured-data[type="application/ld+json"]',
    ];
    for (const selector of selectors) {
      document.querySelectorAll(selector).forEach(node => node.remove());
      nextDocument.querySelectorAll(selector).forEach(node => document.head.append(node));
    }
  };

  const navigate = async requestedUrl => {
    activeController?.abort();
    const controller = new AbortController();
    activeController = controller;
    const signal = controller.signal;
    let timedOut = false;

    const status = document.getElementById("navigation-status");
    const currentMain = document.getElementById("site-content");
    if (status) status.textContent = "Loading page.";
    currentMain?.setAttribute("aria-busy", "true");

    const fallbackTimer = window.setTimeout(() => {
      if (activeController !== controller || signal.aborted) return;
      timedOut = true;
      controller.abort();
      location.assign(requestedUrl.href);
    }, 5000);

    try {
      const response = await fetch(requestedUrl.href, {
        credentials: "same-origin",
        signal,
        headers: { "X-Spirit-Navigation": "1" },
      });
      const html = await response.text();
      if (signal.aborted || activeController !== controller) return;

      const nextDocument = new DOMParser().parseFromString(html, "text/html");
      const nextMain = nextDocument.getElementById("site-content");
      const resolved = new URL(response.url, location.href);
      if (!response.ok || !nextMain || !isInternalPublicUrl(resolved)) {
        location.assign(resolved.href);
        return;
      }

      document.getElementById("photo-dialog")?.close?.();
      currentMain?.replaceWith(nextMain);
      nextMain.setAttribute("aria-busy", "false");

      const nextNav = nextDocument.getElementById("public-nav");
      if (nextNav) nav.innerHTML = nextNav.innerHTML;
      const nextFooter = nextDocument.querySelector(".site-footer");
      if (nextFooter) document.querySelector(".site-footer")?.replaceWith(nextFooter);

      document.title = nextDocument.title;
      replaceHeadMetadata(nextDocument);
      history.pushState({ spirit: true }, "", resolved.href + requestedUrl.hash);
      closeMenu();

      const anchor = requestedUrl.hash
        ? document.getElementById(decodeURIComponent(requestedUrl.hash.slice(1)))
        : null;
      if (anchor) anchor.scrollIntoView();
      else window.scrollTo(0, 0);
      nextMain.focus({ preventScroll: true });

      if (status) status.textContent = "Page loaded.";
      document.dispatchEvent(new CustomEvent("spirit:navigated", {
        detail: { root: nextMain },
      }));
    } catch (error) {
      if (timedOut || activeController !== controller) return;
      if (error?.name !== "AbortError") location.assign(requestedUrl.href);
    } finally {
      clearTimeout(fallbackTimer);
      if (activeController === controller) activeController = null;
    }
  };

  /* Capture navigation before sticky-header layout changes can retarget the
     bubbling click in WebKit. This listener owns only ordinary public-nav link
     activations; modified clicks and external/private destinations keep their
     native browser behavior. */
  document.addEventListener("click", event => {
    const target = event.target instanceof Element ? event.target : null;
    const link = target?.closest("#public-nav a[href]");
    if (!link || event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    if (link.target || link.hasAttribute("download")) return;

    const url = new URL(link.href, location.href);
    if (!isInternalPublicUrl(url)) return;
    if (url.pathname === location.pathname && url.search === location.search && url.hash) return;

    event.preventDefault();
    event.stopImmediatePropagation();
    closeMenu();
    void navigate(url);
  }, true);
})();
