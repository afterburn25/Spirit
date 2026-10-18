"use strict";
(() => {
  const toggle = document.querySelector('.nav-toggle');
  const nav = document.getElementById('public-nav');
  const player = document.getElementById('persistent-player');
  if (!toggle || !nav || !player) return;

  const phone = window.matchMedia('(max-width: 820px)');

  const clearBounds = () => {
    nav.style.removeProperty('--menu-max-height');
    nav.style.removeProperty('height');
    nav.style.removeProperty('max-height');
  };

  const fitShortPhoneMenu = () => {
    if (!phone.matches || !nav.classList.contains('is-open')) {
      clearBounds();
      return 0;
    }

    const viewport = window.visualViewport;
    const viewportBottom = Math.min(
      window.innerHeight,
      viewport ? viewport.offsetTop + viewport.height : window.innerHeight,
    );
    const playerTop = player.hidden ? viewportBottom - 16 : player.getBoundingClientRect().top - 12;
    const navTop = nav.getBoundingClientRect().top;
    const available = Math.max(88, Math.floor(playerTop - navTop));

    /* Keep the disclosure genuinely scrollable on a 320 x 568 phone and keep
       its lower edge safely above the persistent player. Setting an explicit
       height, not only a max-height variable, prevents flex sizing differences
       between Chromium, Firefox and WebKit from expanding it behind the player. */
    const shortPhoneCap = Math.max(176, Math.min(240, Math.floor(window.innerHeight * 0.39)));
    const height = Math.max(88, Math.min(available, shortPhoneCap));
    const pixels = `${height}px`;
    nav.style.setProperty('--menu-max-height', pixels);
    nav.style.setProperty('height', pixels, 'important');
    nav.style.setProperty('max-height', pixels, 'important');
    return height;
  };

  const reveal = (link, toEnd = false) => {
    fitShortPhoneMenu();
    if (!link) return;
    if (toEnd) nav.scrollTop = nav.scrollHeight;
    else nav.scrollTop = 0;
    link.focus({ preventScroll: true });
    link.scrollIntoView({ block: 'nearest', inline: 'nearest' });
    if (toEnd) nav.scrollTop = nav.scrollHeight;
  };

  toggle.addEventListener('click', () => requestAnimationFrame(fitShortPhoneMenu));
  toggle.addEventListener('keydown', event => {
    if (!phone.matches || !['ArrowDown', 'ArrowUp'].includes(event.key)) return;
    const toEnd = event.key === 'ArrowUp';
    const link = toEnd
      ? nav.querySelector('a:last-of-type')
      : nav.querySelector('a:first-of-type');

    /* public.js opens the disclosure first. Run synchronously afterwards so
       the browser-test and keyboard user both see the final bounded geometry
       before focus moves on to another task. */
    reveal(link, toEnd);
  });

  nav.addEventListener('focusin', event => {
    if (!(event.target instanceof HTMLElement) || !event.target.matches('a[href]')) return;
    const toEnd = event.target === nav.querySelector('a:last-of-type');
    reveal(event.target, toEnd);
  });

  new MutationObserver(fitShortPhoneMenu).observe(nav, {
    attributes: true,
    attributeFilter: ['class'],
  });
  window.addEventListener('resize', fitShortPhoneMenu);
  window.visualViewport?.addEventListener('resize', fitShortPhoneMenu);
  if ('ResizeObserver' in window) new ResizeObserver(fitShortPhoneMenu).observe(player);
})();
