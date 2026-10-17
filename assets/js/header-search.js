'use strict';
(() => {
  const panel = document.getElementById('site-search-panel');
  const input = document.getElementById('site-search-input');
  if (!panel || !input) return;

  const toggles = () => Array.from(document.querySelectorAll('.nav-search-toggle'));
  const currentToggle = () => document.querySelector('.nav-search-toggle');
  const isOpen = () => !panel.hidden;

  function closeMobileNavigation() {
    const nav = document.getElementById('public-nav');
    const toggle = document.querySelector('.nav-toggle');
    nav?.classList.remove('is-open');
    nav?.style.removeProperty('--menu-max-height');
    toggle?.setAttribute('aria-expanded', 'false');
  }

  function focusSearchInput() {
    input.focus({ preventScroll: true });
    input.select();
  }

  function setOpen(open, returnFocus = false) {
    const show = Boolean(open);
    panel.hidden = !show;
    document.body.classList.toggle('site-search-open', show);
    toggles().forEach(button => {
      button.setAttribute('aria-expanded', String(show));
      button.setAttribute('aria-label', show ? 'Close site search' : 'Open site search');
    });
    if (show) {
      closeMobileNavigation();
      focusSearchInput();
      requestAnimationFrame(() => {
        if (document.activeElement !== input) focusSearchInput();
      });
    } else if (returnFocus) {
      currentToggle()?.focus({ preventScroll: true });
    }
  }

  document.addEventListener('click', event => {
    const close = event.target.closest('.site-search-close');
    if (close) {
      event.preventDefault();
      setOpen(false, true);
      return;
    }

    const button = event.target.closest('.nav-search-toggle');
    if (button) {
      event.preventDefault();
      setOpen(!isOpen(), false);
      return;
    }

    if (isOpen() && !panel.contains(event.target)) setOpen(false, false);
  });

  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && isOpen()) {
      event.preventDefault();
      setOpen(false, true);
      return;
    }
    if (event.key === '/' && !isOpen() && !event.ctrlKey && !event.metaKey && !event.altKey) {
      const tag = document.activeElement?.tagName?.toLowerCase();
      if (!['input','textarea','select'].includes(tag) && !document.activeElement?.isContentEditable) {
        event.preventDefault();
        setOpen(true, false);
      }
    }
  });

  panel.querySelector('form')?.addEventListener('submit', () => setOpen(false, false));
  panel.querySelectorAll('a').forEach(link => link.addEventListener('click', () => setOpen(false, false)));
  document.addEventListener('spirit:navigated', () => setOpen(false, false));
})();
