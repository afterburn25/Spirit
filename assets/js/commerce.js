'use strict';
// Delegated listeners also work after persistent-player public navigation.
document.addEventListener('change', event => {
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
document.addEventListener('click', event => {
  const button = event.target.closest('[data-product-photo]');
  if (!button) return;
  const target = document.querySelector('.product-main-image');
  if (target) target.src = button.dataset.productPhoto;
});
