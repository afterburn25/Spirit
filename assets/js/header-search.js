"use strict";
(() => {
  const root = document.querySelector('[data-header-search]');
  if (!root) return;
  const button = root.querySelector('.nav-search');
  const panel = root.querySelector('.header-search-panel');
  const input = root.querySelector('input[type="search"]');
  const nav = document.getElementById('public-nav');
  const navToggle = document.querySelector('.nav-toggle');
  let returnFocus = null;

  const opened = () => !panel.hidden;
  const close = (restore = false) => {
    if (!opened()) return;
    panel.hidden = true;
    button.setAttribute('aria-expanded', 'false');
    button.setAttribute('aria-label', 'Open site search');
    if (restore) (returnFocus || button).focus({ preventScroll: true });
    returnFocus = null;
  };
  const open = () => {
    returnFocus = document.activeElement;
    panel.hidden = false;
    button.setAttribute('aria-expanded', 'true');
    button.setAttribute('aria-label', 'Close site search');
    nav?.classList.remove('is-open');
    navToggle?.setAttribute('aria-expanded', 'false');
    requestAnimationFrame(() => input?.focus({ preventScroll: true }));
  };

  button.addEventListener('click', event => {
    event.preventDefault();
    event.stopPropagation();
    opened() ? close(true) : open();
  });
  panel.addEventListener('click', event => event.stopPropagation());
  document.addEventListener('click', event => {
    if (opened() && !root.contains(event.target)) close(false);
  });
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && opened()) {
      event.preventDefault();
      close(true);
    }
  });
  navToggle?.addEventListener('click', () => close(false));
  window.addEventListener('resize', () => {
    if (window.innerWidth <= 360 && opened()) panel.scrollIntoView({ block: 'nearest' });
  });
  document.addEventListener('spirit:navigated', () => close(false));
})();
