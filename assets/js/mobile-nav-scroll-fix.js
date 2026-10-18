"use strict";
(() => {
  const toggle = document.querySelector('.nav-toggle');
  const nav = document.getElementById('public-nav');
  const player = document.getElementById('persistent-player');
  if (!toggle || !nav || !player) return;

  const phone = window.matchMedia('(max-width: 820px)');

  const fitShortPhoneMenu = () => {
    if (!phone.matches || !nav.classList.contains('is-open')) {
      nav.style.removeProperty('--menu-max-height');
      return;
    }

    const viewport = window.visualViewport;
    const viewportBottom = Math.min(
      window.innerHeight,
      viewport ? viewport.offsetTop + viewport.height : window.innerHeight,
    );
    const playerTop = player.hidden ? viewportBottom - 16 : player.getBoundingClientRect().top - 12;
    const navTop = nav.getBoundingClientRect().top;
    const available = Math.max(88, Math.floor(playerTop - navTop));

    /* A short phone must retain a genuinely scrollable disclosure above the
       persistent player. Capping the menu to roughly half the viewport prevents
       browser focus scrolling from pushing the disclosure behind the player. */
    const shortPhoneCap = Math.max(176, Math.floor(window.innerHeight * 0.46));
    nav.style.setProperty('--menu-max-height', `${Math.min(available, shortPhoneCap)}px`);
  };

  const reveal = link => {
    fitShortPhoneMenu();
    if (!link) return;
    link.scrollIntoView({ block: 'nearest', inline: 'nearest' });
  };

  toggle.addEventListener('click', () => requestAnimationFrame(fitShortPhoneMenu));
  toggle.addEventListener('keydown', event => {
    if (!phone.matches || !['ArrowDown', 'ArrowUp'].includes(event.key)) return;
    const link = event.key === 'ArrowUp'
      ? nav.querySelector('a:last-of-type')
      : nav.querySelector('a:first-of-type');
    requestAnimationFrame(() => reveal(link));
  });

  nav.addEventListener('focusin', event => {
    if (event.target instanceof HTMLElement && event.target.matches('a[href]')) reveal(event.target);
  });

  window.addEventListener('resize', fitShortPhoneMenu);
  window.visualViewport?.addEventListener('resize', fitShortPhoneMenu);
  if ('ResizeObserver' in window) new ResizeObserver(fitShortPhoneMenu).observe(player);
})();
