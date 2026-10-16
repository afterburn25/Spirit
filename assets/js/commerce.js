'use strict';

const syncBillingAddress = root => {
  const same = root.querySelector('input[name="billing_same"]');
  if (!(same instanceof HTMLInputElement)) return;
  const form = same.form;
  if (!form) return;
  const details = same.closest('form')?.querySelector('details.size-guide');
  const fields = ['billing_line','billing_city','billing_region','billing_zip']
    .map(name => form.elements.namedItem(name))
    .filter(field => field instanceof HTMLInputElement || field instanceof HTMLSelectElement);
  const separate = !same.checked;
  fields.forEach(field => {
    field.required = separate;
    field.setAttribute('aria-required', String(separate));
  });
  if (separate && details instanceof HTMLDetailsElement) details.open = true;
};
syncBillingAddress(document);
document.addEventListener('spirit:navigated', event => syncBillingAddress(event.detail.root));

// Delegated listeners also work after persistent-player public navigation.
document.addEventListener('change', event => {
  if (event.target.matches('input[name="billing_same"]')) {
    syncBillingAddress(event.target.form || document);
    return;
  }
  if (!event.target.matches('#product-variant')) return;
  const option = event.target.selectedOptions[0];
  const price = document.getElementById('variant-price');
  const stock = document.getElementById('variant-stock');
  const button = document.getElementById('product-add');
  if (!option || !price || !stock || !button) return;
  price.textContent = `${option.dataset.price} USD`;
  const available = Number(option.dataset.stock) > 0;
  stock.textContent = available ? 'In stock' : 'Out of stock';
  button.disabled = !available;
});
// A responsive gallery must switch the complete image, not only its fallback src.
// Keep the old image until decoding succeeds, and ignore superseded requests.
const galleryRequests = new WeakMap();
document.addEventListener('click', async event => {
  const link = event.target.closest('a[data-product-photo]');
  if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
  const gallery = link.closest('.product-gallery');
  const target = gallery?.querySelector('.product-main');
  const source = document.getElementById(link.dataset.photoSource)?.content?.querySelector('img');
  const status = gallery?.querySelector('.product-photo-status');
  if (!target || !source || !status || !('decode' in HTMLImageElement.prototype)) return;
  const url = new URL(link.href, location.href);
  if (url.origin !== location.origin || url.pathname !== location.pathname) return;
  event.preventDefault();
  const request = {};
  galleryRequests.set(gallery, request);
  const current = () => gallery.isConnected && galleryRequests.get(gallery) === request;
  if (link.hasAttribute('aria-current')) {
    target.setAttribute('aria-busy', 'false');
    status.textContent = `Showing ${link.dataset.photoLabel.toLowerCase()}.`;
    return;
  }
  target.setAttribute('aria-busy', 'true');
  status.textContent = `Loading ${link.dataset.photoLabel.toLowerCase()}.`;
  const image = document.importNode(source, true);
  let timer;
  try {
    await Promise.race([
      image.decode(),
      new Promise((_, reject) => { timer = setTimeout(() => reject(new Error('Image timeout')), 12000); })
    ]);
    if (!current()) return;
    if (!image.naturalWidth) throw new Error('Image unavailable');
    target.replaceChildren(image);
    gallery.querySelectorAll('.product-photo').forEach(item => item.removeAttribute('aria-current'));
    link.setAttribute('aria-current', 'true');
    status.textContent = `Showing ${link.dataset.photoLabel.toLowerCase()}.`;
    // Replace only this entry: changing product photos must not fill Back history.
    // Do not touch form values, audio, metadata or other application state.
    try { history.replaceState(history.state, '', url.href); } catch { /* History may be rate-limited; the decoded image remains valid. */ }
  } catch {
    if (current()) status.textContent = 'This photograph could not load. Your previous photograph is still displayed. Select a photograph to try again.';
  } finally {
    clearTimeout(timer);
    if (current()) target.setAttribute('aria-busy', 'false');
  }
});
