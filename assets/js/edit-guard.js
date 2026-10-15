"use strict";
(() => {
  // Only explicitly marked authoring forms participate. This is a warning, not
  // autosave: values are compared in memory, never logged, transmitted or stored.
  const allowed = new Set(["text", "search", "number", "email", "url", "tel", "date",
    "datetime-local", "month", "time", "week", "checkbox", "radio", "range", "color"]);
  const controls = form => Array.from(form.elements).filter(node => {
    if (!node.name || node.disabled || node.hasAttribute("data-edit-guard-ignore")) return false;
    if (node instanceof HTMLInputElement) return allowed.has(node.type);
    return node instanceof HTMLTextAreaElement || node instanceof HTMLSelectElement;
  });
  const values = (form, defaults = false) => JSON.stringify(controls(form).map(node => {
    let value;
    if (node instanceof HTMLSelectElement) {
      const selected = Array.from(node.options).filter(option => defaults ? option.defaultSelected : option.selected);
      if (defaults && !node.multiple && selected.length === 0 && node.options.length) {
        const first = Array.from(node.options).find(option => !option.disabled && !option.parentElement.disabled);
        if (first) selected.push(first);
      }
      value = selected.map(option => option.value);
    } else if (node.type === "checkbox" || node.type === "radio") {
      value = defaults ? node.defaultChecked : node.checked;
    } else {
      value = defaults ? node.defaultValue : node.value;
    }
    return [node.name, value];
  }));
  const states = Array.from(document.querySelectorAll("form[data-edit-guard]")).map(form => {
    const notice = document.createElement("p");
    notice.className = "edit-guard-notice";
    notice.setAttribute("role", "status");
    notice.setAttribute("aria-live", "polite");
    notice.hidden = true;
    form.prepend(notice);
    return {form, notice, baseline: values(form, true),
      rejected: form.dataset.editGuardDirty === "true", dirty: false, submitting: false, submissionEvent: null};
  });
  if (!states.length) return;
  let listening = false;
  const beforeLeave = event => {
    // Check again for browser autofill or a changed control before navigation.
    if (!states.some(state => !(state.submitting && !state.submissionEvent?.defaultPrevented) && (state.rejected || values(state.form) !== state.baseline))) return;
    event.preventDefault();
    event.returnValue = ""; // Browser chooses its own warning text.
  };
  const reconcile = () => {
    for (const state of states) {
      state.dirty = state.rejected || values(state.form) !== state.baseline;
      state.form.dataset.editGuardState = state.submitting ? "submitting" : state.dirty ? "dirty" : "clean";
      state.notice.hidden = !state.dirty;
      const text = state.submitting ? "Submitting changes. Wait for the website's confirmation." :
        state.rejected ? "Your submission was not saved. Review the error before leaving this editor." :
        "Unsaved changes. Save before leaving; a preview shows the last saved draft.";
      if (state.notice.textContent !== text) state.notice.textContent = text;
    }
    const needed = states.some(state => state.dirty);
    if (needed !== listening) {
      window[needed ? "addEventListener" : "removeEventListener"]("beforeunload", beforeLeave);
      listening = needed;
    }
  };
  for (const state of states) {
    state.form.addEventListener("input", reconcile);
    state.form.addEventListener("change", reconcile);
    // The reset default action runs after the event; compare restored values.
    state.form.addEventListener("reset", () => setTimeout(reconcile, 0));
    state.form.addEventListener("submit", event => {
      // Native validation must succeed first. Exempt only this marked POST,
      // never logout/other forms or a submission targeting a different window.
      const target = event.submitter?.formTarget || state.form.target;
      const method = event.submitter?.formMethod || state.form.method;
      if (event.defaultPrevented || method.toLowerCase() !== "post" || (target && target !== "_self")) return;
      state.submissionEvent = event;
      state.submitting = true;
      reconcile();
      // Other submit listeners may still cancel it. beforeLeave also checks the
      // final event flag synchronously; a cancellation never grants a bypass.
      setTimeout(() => {
        if (event.defaultPrevented) state.submitting = false;
        reconcile();
      }, 0);
    });
  }
  // Returning through browser history must not preserve a submission bypass.
  // Default values still describe server-rendered fields; restored input values
  // can differ. A rejected response is never represented as saved.
  window.addEventListener("pageshow", () => {
    states.forEach(state => { state.submitting = false; });
    reconcile();
  });
  reconcile();
})();
