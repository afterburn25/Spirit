"use strict";
(() => {
  const nav = document.getElementById("public-nav");
  const header = document.querySelector(".site-header");
  const toggle = document.querySelector(".nav-toggle");
  if (!nav || !header || !toggle) return;

  const originalParent = nav.parentNode;
  const originalNextSibling = nav.nextSibling;
  const phone = window.matchMedia("(max-width: 820px)");

  /* The disclosure is rendered as a body-level portal on phones so the sticky
     header can never sit on top of its links. The header height is responsive
     (logo, Search and browser text sizing can all change it), therefore the
     portal top must be measured rather than hard-coded. */
  const syncBounds = () => {
    if (!phone.matches || !nav.classList.contains("mobile-nav-portal")) {
      nav.style.removeProperty("--mobile-nav-top");
      return;
    }
    const bottom = Math.ceil(Math.max(0, header.getBoundingClientRect().bottom + 8));
    nav.style.setProperty("--mobile-nav-top", `${bottom}px`);
  };

  const ensurePhonePortal = () => {
    if (!phone.matches) return;
    if (nav.parentNode !== document.body) document.body.append(nav);
    nav.classList.add("mobile-nav-portal");
    document.body.classList.add("has-mobile-nav-portal");
    syncBounds();
  };

  const place = () => {
    if (phone.matches) {
      ensurePhonePortal();
      requestAnimationFrame(syncBounds);
      return;
    }

    nav.classList.remove("mobile-nav-portal", "is-open");
    nav.style.removeProperty("--mobile-nav-top");
    nav.style.removeProperty("--menu-max-height");
    nav.style.removeProperty("height");
    nav.style.removeProperty("max-height");
    document.body.classList.remove("has-mobile-nav-portal");
    if (nav.parentNode !== originalParent) {
      if (originalNextSibling?.parentNode === originalParent) originalParent.insertBefore(nav, originalNextSibling);
      else originalParent.append(nav);
    }
  };

  /* Moving the nav to the end of <body> changes the browser's natural tab
     sequence. Preserve the disclosure's logical order around its Menu button. */
  toggle.addEventListener("keydown", event => {
    if (!phone.matches || event.key !== "Tab" || event.shiftKey || !nav.classList.contains("is-open")) return;
    const first = nav.querySelector("a[href]");
    if (!first) return;
    event.preventDefault();
    first.focus({ preventScroll: true });
    first.scrollIntoView({ block: "nearest", inline: "nearest" });
  });

  nav.addEventListener("keydown", event => {
    if (!phone.matches || event.key !== "Tab" || !nav.classList.contains("is-open")) return;
    const links = Array.from(nav.querySelectorAll("a[href]"));
    const first = links[0];
    const last = links[links.length - 1];

    if (event.shiftKey && event.target === first) {
      event.preventDefault();
      toggle.focus({ preventScroll: true });
      return;
    }

    if (!event.shiftKey && event.target === last) {
      event.preventDefault();
      const search = document.querySelector(".nav-search");
      const destination = search instanceof HTMLElement && search.getClientRects().length
        ? search
        : document.getElementById("site-content");
      destination?.focus({ preventScroll: true });
    }
  });

  /* A matchMedia change can be delivered after an immediate scripted click.
     Re-establish the body portal synchronously before the disclosure opens so
     no sticky-header descendant can intercept the first navigation click. */
  toggle.addEventListener("pointerdown", ensurePhonePortal, true);
  toggle.addEventListener("click", () => {
    ensurePhonePortal();
    requestAnimationFrame(syncBounds);
  }, true);

  window.addEventListener("resize", syncBounds);
  window.addEventListener("scroll", syncBounds, { passive: true });
  window.visualViewport?.addEventListener("resize", syncBounds);
  window.visualViewport?.addEventListener("scroll", syncBounds);
  document.addEventListener("spirit:navigated", syncBounds);
  if ("ResizeObserver" in window) new ResizeObserver(syncBounds).observe(header);

  place();
  phone.addEventListener("change", place);
})();
