'use strict';
const toggle = document.querySelector('.menu-toggle');
const sidebar = document.getElementById('sidebar');
if (toggle && sidebar) {
  const close = () => { sidebar.classList.remove('is-open'); toggle.setAttribute('aria-expanded', 'false'); };
  toggle.addEventListener('click', () => {
    const open = toggle.getAttribute('aria-expanded') !== 'true';
    sidebar.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', String(open));
  });
  document.addEventListener('keydown', event => { if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') { close(); toggle.focus(); } });
  document.addEventListener('click', event => { if (!sidebar.contains(event.target) && !toggle.contains(event.target)) close(); });
}
const install = document.getElementById('install-form');
if (install) {
  install.addEventListener('submit', () => {
    const button = install.querySelector('button[type="submit"]');
    button.disabled = true;
    button.textContent = 'Installing…';
    document.getElementById('install-status').hidden = false;
  });
}
