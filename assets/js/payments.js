'use strict';
(() => {
  const form = document.getElementById('card-payment');
  if (!form) return;
  const button = document.getElementById('card-pay-button');
  const message = document.getElementById('payment-message');
  const sensitive = ['card-holder', 'card-number', 'card-month', 'card-year', 'card-code'];
  let busy = false;
  const clear = () => sensitive.forEach(id => { document.getElementById(id).value = ''; });
  const available = window.Accept && typeof window.Accept.dispatchData === 'function';
  button.disabled = !available;
  message.textContent = available ? 'Payment details are tokenized directly by Authorize.net.' : 'Secure payment library unavailable. Reload this page; do not email card details.';
  // Native submission must never carry raw card fields, even on Enter/JS failure.
  form.addEventListener('submit', event => { event.preventDefault(); if (!busy) button.click(); });
  button.addEventListener('click', () => {
    if (busy || !available || !form.reportValidity()) return;
    busy = true; button.disabled = true;
    message.textContent = 'Tokenizing payment securely…';
    const cardData = {
      cardNumber: document.getElementById('card-number').value.replace(/[\s-]/g, ''),
      month: document.getElementById('card-month').value,
      year: document.getElementById('card-year').value,
      cardCode: document.getElementById('card-code').value,
      fullName: document.getElementById('card-holder').value,
    };
    try {
      window.Accept.dispatchData({authData: {apiLoginID: form.dataset.login, clientKey: form.dataset.client}, cardData}, response => {
        clear();
        if (!response || response.messages?.resultCode !== 'Ok' || !response.opaqueData?.dataValue) {
          busy = false; button.disabled = false;
          message.textContent = 'Payment details were not tokenized. No charge was submitted. Check the details and try again.';
          return;
        }
        document.getElementById('payment-descriptor').value = response.opaqueData.dataDescriptor;
        document.getElementById('payment-value').value = response.opaqueData.dataValue;
        message.textContent = 'Submitting this payment once. Do not close or refresh the page.';
        // Only named hidden token/CSRF/nonce fields are serialized.
        HTMLFormElement.prototype.submit.call(form);
      });
    } catch {
      clear(); busy = false; button.disabled = false;
      message.textContent = 'The secure payment service was unavailable. No charge was submitted.';
    }
  });
  window.addEventListener('pagehide', clear);
  window.addEventListener('pageshow', event => {
    if (event.persisted) { clear(); location.reload(); }
  });
})();
