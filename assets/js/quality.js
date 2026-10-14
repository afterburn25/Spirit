'use strict';
(() => {
  // Progressive feedback only: do not inspect, log or replay form-field values.
  const enhance = root => {
    const error = root.querySelector('.form-notice.error[role="alert"], .notice.error[role="alert"]');
    if (error && error.textContent.trim()) {
      error.tabIndex = -1;
      error.focus();
    }
    root.querySelectorAll('thead th:not([scope])').forEach(cell => cell.setAttribute('scope', 'col'));
  };
  enhance(document);
  document.addEventListener('spirit:navigated', event => enhance(event.detail.root));
  // Keep keyboard focus visible above the persistent player. Do not scroll a
  // modal dialog or steal focus merely because Escape is pressed elsewhere.
  document.addEventListener('focusin', event => {
    const player = document.getElementById('persistent-player');
    const target = event.target;
    if (!player || player.hidden || !(target instanceof HTMLElement) || player.contains(target) || target.closest('dialog')) return;
    const rect = target.getBoundingClientRect(), obstruction = player.getBoundingClientRect();
    if (rect.bottom > obstruction.top && rect.top < innerHeight) target.scrollIntoView({ block: 'center', behavior: 'instant' });
  });
})();
