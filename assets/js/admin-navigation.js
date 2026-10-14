'use strict';
(() => {
  const sidebar = document.getElementById('sidebar');
  if (!sidebar?.matches('.grouped-sidebar')) return;
  const scroller = document.getElementById('admin-nav-scroll');
  const toggle = document.querySelector('.admin-menu-toggle');
  const closeButton = sidebar.querySelector('.admin-sidebar-close');
  const backdrop = document.querySelector('.admin-nav-backdrop');
  const workspace = document.querySelector('.workspace');
  const memory = document.getElementById('admin-nav-memory');
  const groups = Array.from(sidebar.querySelectorAll('[data-nav-group]'));
  const scope = sidebar.dataset.navScope;
  if (!scroller || !toggle || !workspace || !/^[a-f0-9]{32}$/.test(scope || '')) return;
  const prefix = `spirit:admin-nav:v1:${scope}`;
  const media = window.matchMedia('(max-width: 800px)');
  const has = (object, name) => Object.prototype.hasOwnProperty.call(object, name);
  const object = value => value !== null && typeof value === 'object' && !Array.isArray(value);
  let mobileOpen = false, frame = 0, restoring = true, saveUnavailable = false;
  const remembered = new Map();
  const unavailable = () => {
    if (saveUnavailable) return;
    saveUnavailable = true;
    memory.textContent = 'Menu works; this browser cannot save its layout.';
  };
  const read = (storage, key) => {
    try {
      const raw = window[storage].getItem(key);
      if (!raw || raw.length > 4096) return null;
      const data = JSON.parse(raw);
      return object(data) && data.version === 1 ? data : null;
    } catch (error) {
      // Corrupt JSON is ignored; unavailable browser storage does not disable navigation.
      if (!(error instanceof SyntaxError)) unavailable();
      return null;
    }
  };
  const write = (storage, key, data) => {
    try { window[storage].setItem(key, JSON.stringify(data)); }
    catch { unavailable(); }
  };
  function saveGroups() {
    const values = {};
    groups.forEach(group => {
      values[group.dataset.navGroup] = group.open;
      remembered.set(group.dataset.navGroup, group.open);
    });
    write('localStorage', prefix, { version: 1, groups: values });
  }
  function savePosition() {
    if (restoring) return;
    write('sessionStorage', `${prefix}:view`, {
      version: 1, scroll: Math.max(0, Math.min(10000000, scroller.scrollTop)), mobileOpen
    });
  }
  function paintDrawer() {
    const shown = media.matches && mobileOpen;
    sidebar.classList.toggle('is-open', shown);
    sidebar.inert = media.matches && !shown;
    workspace.inert = shown;
    backdrop.hidden = !shown;
    toggle.setAttribute('aria-expanded', String(shown));
    toggle.setAttribute('aria-label', shown ? 'Close administration menu' : 'Open administration menu');
    document.documentElement.classList.toggle('admin-nav-open', shown);
  }
  function drawer(open, returnFocus = false) {
    mobileOpen = Boolean(open);
    paintDrawer(); savePosition();
    if (returnFocus) toggle.focus({ preventScroll: true });
  }
  function restore() {
    restoring = true;
    const preferences = read('localStorage', prefix);
    groups.forEach(group => {
      const id = group.dataset.navGroup;
      const values = preferences?.groups;
      group.open = object(values) && has(values, id) && typeof values[id] === 'boolean' ? values[id] : true;
      remembered.set(id, group.open);
    });
    const view = read('sessionStorage', `${prefix}:view`);
    mobileOpen = typeof view?.mobileOpen === 'boolean' ? view.mobileOpen : false;
    paintDrawer();
    const validScroll = typeof view?.scroll === 'number' && Number.isFinite(view.scroll) && view.scroll >= 0 && view.scroll <= 10000000;
    const position = () => {
      if (validScroll) scroller.scrollTop = Math.min(view.scroll, Math.max(0, scroller.scrollHeight - scroller.clientHeight));
      else {
        // Reveal a first-visit active link by scrolling only the sidebar, never the page.
        const active = sidebar.querySelector('a[aria-current="page"]');
        if (active && active.getClientRects().length) {
          const target = active.getBoundingClientRect(), bounds = scroller.getBoundingClientRect();
          if (target.bottom > bounds.bottom - 8) scroller.scrollTop += target.bottom - bounds.bottom + 12;
          else if (target.top < bounds.top + 8) scroller.scrollTop += target.top - bounds.top - 12;
        }
      }
    };
    position();
    requestAnimationFrame(() => { position(); restoring = false; });
  }
  // Ordinary disclosure links, not ARIA application/menu roles. Native details
  // remains usable if this script or browser storage is unavailable.
  sidebar.querySelector('.admin-nav-tools').hidden = false;
  memory.hidden = false; toggle.hidden = false; closeButton.hidden = false;
  document.documentElement.classList.add('admin-nav-enhanced');
  restore();
  groups.forEach(group => group.addEventListener('toggle', () => {
    if (remembered.get(group.dataset.navGroup) === group.open) return;
    saveGroups(); savePosition();
  }));
  sidebar.querySelectorAll('[data-nav-action]').forEach(button => button.addEventListener('click', () => {
    groups.forEach(group => { group.open = button.dataset.navAction === 'expand'; });
    saveGroups(); savePosition();
  }));
  toggle.addEventListener('click', () => drawer(!mobileOpen));
  closeButton.addEventListener('click', () => drawer(false, true));
  backdrop.addEventListener('click', () => drawer(false, true));
  media.addEventListener('change', () => {
    paintDrawer();
    if (sidebar.inert && sidebar.contains(document.activeElement)) toggle.focus({ preventScroll: true });
  });
  document.addEventListener('keydown', event => {
    if (!media.matches || !mobileOpen) return;
    if (event.key === 'Escape') { event.preventDefault(); drawer(false, true); return; }
    if (event.key !== 'Tab') return;
    const targets = [toggle, ...sidebar.querySelectorAll('a[href],button,summary,input:not([type="hidden"])')]
      .filter(node => !node.disabled && node.getClientRects().length && getComputedStyle(node).visibility !== 'hidden');
    const first = targets[0], last = targets[targets.length - 1];
    if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
    else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
  });
  sidebar.addEventListener('click', event => {
    const link = event.target.closest('a[href]');
    if (!link || event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || link.target || link.hasAttribute('download')) return;
    saveGroups();
    // Selecting a page reveals its content on a phone. Reload without selecting
    // another page preserves the drawer's current open/closed state instead.
    if (media.matches) drawer(false);
    savePosition();
  });
  scroller.addEventListener('scroll', () => {
    if (frame || restoring) return;
    frame = requestAnimationFrame(() => { frame = 0; savePosition(); });
  }, { passive: true });
  window.addEventListener('pagehide', () => { saveGroups(); savePosition(); });
  window.addEventListener('pageshow', event => { if (event.persisted) restore(); });
  sidebar.dataset.navReady = 'true';
  // Do not change location, History state, form data, authorization or the page's
  // own scroll restoration. Preferences contain only booleans and sidebar pixels.
})();
