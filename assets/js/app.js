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
// Both database choices remain ordinary fields on the same native form.
// Without JavaScript, the server validates only the selected method.
const databaseMode = document.getElementById('db-mode');
if (databaseMode) {
  const groups = {cpanel: document.getElementById('cpanel-fields'), existing: document.getElementById('existing-db-fields')};
  const applyMode = () => Object.entries(groups).forEach(([mode, group]) => {
    const selected = databaseMode.value === mode;
    group.hidden = !selected;
    group.querySelectorAll('input').forEach(input => {
      input.disabled = !selected;
      input.required = selected && input.hasAttribute('data-mode-required');
      if (!selected && input.type === 'password') input.value = '';
    });
  });
  databaseMode.addEventListener('change', applyMode);
  window.addEventListener('pageshow', applyMode);
  applyMode();
}
const install = document.getElementById('install-form');
if (install) {
  const button = install.querySelector('button[type="submit"]');
  const status = document.getElementById('install-status');
  install.addEventListener('submit', () => {
    button.disabled = true;
    button.textContent = 'Installing…';
    install.setAttribute('aria-busy', 'true');
    status.hidden = false;
  });
  // A back/forward-cache restore must not leave a correctable form disabled.
  window.addEventListener('pageshow', () => {
    button.disabled = install.dataset.ready !== 'true';
    button.textContent = 'Install website →';
    install.removeAttribute('aria-busy');
    status.hidden = true;
  });
}
const completedInstall = document.getElementById('install-complete');
if (completedInstall) completedInstall.focus();
