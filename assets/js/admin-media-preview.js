'use strict';
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('select[data-media-picker="image"]').forEach((select) => {
    const preview = document.querySelector('[data-media-preview-for="' + select.id + '"]');
    if (!preview) return;
    const image = preview.querySelector('[data-media-preview-image]');
    const empty = preview.querySelector('[data-media-empty]');
    if (!image || !empty) return;
    const refresh = () => {
      const option = select.selectedOptions[0];
      const src = option?.dataset.mediaSrc || '';
      image.hidden = !src;
      empty.hidden = !!src;
      if (src) {
        image.src = src;
        image.alt = 'Selected image: ' + (option?.textContent?.trim() || 'Preview');
      } else {
        image.removeAttribute('src');
        image.alt = 'Selected image preview';
      }
    };
    select.addEventListener('change', refresh);
    refresh();
  });
});
