"use strict";
(() => {
  const nav = document.getElementById("public-nav");
  const header = document.querySelector(".site-header");
  if (!nav || !header) return;

  const originalParent = nav.parentNode;
  const originalNextSibling = nav.nextSibling;
  const phone = window.matchMedia("(max-width: 820px)");

  const place = () => {
    if (phone.matches) {
      if (nav.parentNode !== document.body) document.body.append(nav);
      nav.classList.add("mobile-nav-portal");
      document.body.classList.add("has-mobile-nav-portal");
      return;
    }

    nav.classList.remove("mobile-nav-portal", "is-open");
    nav.style.removeProperty("--menu-max-height");
    nav.style.removeProperty("height");
    nav.style.removeProperty("max-height");
    document.body.classList.remove("has-mobile-nav-portal");
    if (nav.parentNode !== originalParent) {
      if (originalNextSibling?.parentNode === originalParent) originalParent.insertBefore(nav, originalNextSibling);
      else originalParent.append(nav);
    }
  };

  place();
  phone.addEventListener("change", place);
})();
