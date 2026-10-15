'use strict';
// The inert template belongs to the already authorized, server-rendered page.
// Clone its home link after navigation; do not fetch HTML or touch the player.
document.addEventListener('spirit:navigated', event => {
  const template = event.detail?.root?.querySelector('template[data-site-brand]');
  const next = template?.content?.firstElementChild;
  const current = document.querySelector('.site-header > a.wordmark');
  if (next instanceof HTMLAnchorElement && current) current.replaceWith(next.cloneNode(true));
});
