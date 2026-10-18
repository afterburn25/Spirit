"use strict";
(() => {
  const toggle = document.querySelector('.nav-toggle');
  const nav = document.getElementById('public-nav');
  if (!toggle || !nav) return;
  const phone = window.matchMedia('(max-width: 820px)');

  toggle.addEventListener('keydown', event => {
    if (!phone.matches || !['ArrowDown', 'ArrowUp'].includes(event.key)) return;
    const focused = nav.contains(document.activeElement) ? document.activeElement :
      (event.key === 'ArrowUp' ? nav.querySelector('a:last-of-type') : nav.querySelector('a:first-of-type'));
    focused?.scrollIntoView({ block: 'nearest', inline: 'nearest' });
  });
})();
