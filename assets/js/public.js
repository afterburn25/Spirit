'use strict';
(() => {
  const nav = document.getElementById('public-nav');
  const toggle = document.querySelector('.nav-toggle');
  const audio = document.getElementById('site-audio');
  const player = document.getElementById('persistent-player');
  const status = document.getElementById('player-status');
  const playToggle = document.getElementById('player-toggle');
  const seek = document.getElementById('player-seek');
  let queue = [], current = -1, navigation, photoOpener;
  const previous = document.getElementById('player-previous');
  const nextTrack = document.getElementById('player-next');
  const playerInset = () => { document.documentElement.style.setProperty('--player-height', `${player.hidden ? 0 : Math.ceil(player.getBoundingClientRect().height) + 16}px`); };
  if ('ResizeObserver' in window) new ResizeObserver(playerInset).observe(player);
  else window.addEventListener('resize', playerInset);
  const time = value => Number.isFinite(value) ? `${Math.floor(value / 60)}:${String(Math.floor(value % 60)).padStart(2, '0')}` : '0:00';
  const announce = (message, error = false) => { status.textContent = message; status.className = error ? 'player-error' : 'visually-hidden'; };
  const menu = open => { nav.classList.toggle('is-open', open); toggle.setAttribute('aria-expanded', String(open)); };
  toggle.addEventListener('click', () => menu(toggle.getAttribute('aria-expanded') !== 'true'));
  document.documentElement.classList.add('public-enhanced');
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') { menu(false); toggle.focus(); } });
  const paintPlayback = () => {
    const playing = !audio.paused;
    playToggle.textContent = playing ? 'Ⅱ' : '▶';
    playToggle.setAttribute('aria-label', playing ? 'Pause' : 'Play');
    previous.disabled = current < 0 || (current === 0 && audio.currentTime <= 3);
    nextTrack.disabled = current < 0 || current + 1 >= queue.length;
    playerInset();
    document.querySelectorAll('.play-track').forEach(button => {
      const selected = queue[current] && button.dataset.trackId === queue[current].trackId;
      button.classList.toggle('is-playing', Boolean(selected && playing));
      button.setAttribute('aria-pressed', String(Boolean(selected && playing)));
      button.setAttribute('aria-label', `${selected && playing ? 'Pause' : 'Play'} ${button.dataset.title}`);
    });
  };
  async function play(index) {
    if (index < 0 || index >= queue.length) return;
    current = index;
    const track = queue[index];
    player.hidden = false; document.body.classList.add('has-player');
    document.getElementById('player-title').textContent = track.title;
    document.getElementById('player-release').textContent = track.release;
    audio.src = track.audio; seek.value = '0'; seek.disabled = true;
    announce(`Loading ${track.title}.`);
    try { await audio.play(); announce(`Playing ${track.title}.`); }
    catch { announce('This recording could not play. Try again or choose another track.', true); }
    paintPlayback();
  }
  audio.volume = 0.8;
  playToggle.addEventListener('click', async () => {
    if (!audio.src) return;
    if (audio.paused) { try { await audio.play(); announce('Playback resumed.'); } catch { announce('Playback is unavailable. Try another recording.', true); } }
    else audio.pause();
  });
  audio.addEventListener('play', paintPlayback); audio.addEventListener('pause', paintPlayback);
  audio.addEventListener('error', () => { announce('Recording unavailable. Please try another track.', true); paintPlayback(); });
  const paintTime = () => {
    const ready = Number.isFinite(audio.duration) && audio.duration > 0;
    document.getElementById('player-current').textContent = time(audio.currentTime);
    document.getElementById('player-duration').textContent = time(audio.duration);
    seek.disabled = !ready;
    seek.value = ready ? String(Math.round(audio.currentTime / audio.duration * 1000)) : '0';
    seek.setAttribute('aria-valuetext', ready ? `${time(audio.currentTime)} of ${time(audio.duration)}` : 'Playback time unavailable');
    previous.disabled = current < 0 || (current === 0 && audio.currentTime <= 3);
  };
  // Metadata can arrive before the first throttled timeupdate. Publish accurate
  // accessible duration immediately, and discard stale values when a source ends.
  for (const event of ['loadedmetadata', 'durationchange', 'timeupdate', 'emptied']) audio.addEventListener(event, paintTime);
  audio.addEventListener('ended', () => { if (current + 1 < queue.length) play(current + 1); else paintPlayback(); });
  seek.addEventListener('input', () => { if (Number.isFinite(audio.duration)) audio.currentTime = Number(seek.value) * audio.duration / 1000; });
  document.getElementById('player-volume').addEventListener('input', e => { audio.volume = Math.max(0, Math.min(1, Number(e.target.value))); });
  document.getElementById('player-previous').addEventListener('click', () => { if (audio.currentTime > 3) audio.currentTime = 0; else play(current - 1); });
  document.getElementById('player-next').addEventListener('click', () => play(current + 1));
  document.getElementById('player-close').addEventListener('click', () => {
    const selectedId = queue[current]?.trackId;
    const returnTarget = Array.from(document.querySelectorAll('.play-track')).find(button => button.dataset.trackId === selectedId) || document.getElementById('site-content');
    audio.pause(); audio.removeAttribute('src'); audio.load(); queue = []; current = -1; player.hidden = true; document.body.classList.remove('has-player'); paintPlayback(); returnTarget.focus({ preventScroll: true }); });
  const dialog = document.getElementById('photo-dialog');
  dialog.querySelector('.dialog-close').addEventListener('click', () => dialog.close());
  dialog.addEventListener('click', e => { if (e.target === dialog) dialog.close(); });
  dialog.addEventListener('close', () => {
    document.getElementById('dialog-photo').removeAttribute('src');
    const target = photoOpener?.isConnected ? photoOpener : document.getElementById('site-content');
    target?.focus({ preventScroll: true }); photoOpener = null;
  });
  function internal(url) {
    const home = new URL(document.querySelector('.wordmark').href);
    return !url.searchParams.has('sitemap') && url.origin === location.origin && url.pathname.startsWith(home.pathname) && !/\/(admin|install|account|payments)(\/|$)/.test(url.pathname) && !/\/shop\/(cart|checkout|order|pay)\.php$/.test(url.pathname) && !/\/media\/file\.php$/.test(url.pathname);
  }
  async function navigate(url, push = true) {
    if (navigation) navigation.abort(); navigation = new AbortController();
    const signal = navigation.signal;
    document.getElementById('navigation-status').textContent = 'Loading page.';
    document.getElementById('site-content').setAttribute('aria-busy', 'true');
    try {
      const response = await fetch(url.href, { credentials: 'same-origin', signal, headers: { 'X-Spirit-Navigation': '1' } });
      const html = await response.text();
      if (signal.aborted) return;
      const documentNext = new DOMParser().parseFromString(html, 'text/html');
      const next = documentNext.getElementById('site-content');
      if (!response.ok || !next) { location.assign(url.href); return; }
      const resolved = new URL(response.url);
      if (!internal(resolved)) { location.assign(resolved.href); return; }
      // Only same-origin server-rendered, escaped public content enters this shell.
      if (dialog.open) dialog.close();
      document.getElementById('site-content').replaceWith(next);
      next.setAttribute('aria-busy', 'false');
      const nextNav = documentNext.getElementById('public-nav'); if (nextNav) nav.innerHTML = nextNav.innerHTML;
      // Published policy withdrawal must also update a persistent public shell.
      const nextFooter = documentNext.querySelector('.site-footer');
      if (nextFooter) document.querySelector('.site-footer')?.replaceWith(nextFooter);
      document.title = documentNext.title;
      for (const selector of ['meta[name="description"]', 'meta[name="robots"]', 'link[rel="canonical"]', 'meta[property^="og:"]', 'meta[name^="twitter:"]', 'script#page-structured-data[type="application/ld+json"]']) {
        document.querySelectorAll(selector).forEach(node => node.remove());
        documentNext.querySelectorAll(selector).forEach(node => document.head.append(node));
      }
      if (push) history.pushState({ spirit: true }, '', resolved.href + url.hash);
      else if (location.href !== resolved.href + url.hash) history.replaceState({ spirit: true }, '', resolved.href + url.hash);
      menu(false); paintPlayback();
      const anchor = url.hash ? document.getElementById(decodeURIComponent(url.hash.slice(1))) : null;
      if (anchor) anchor.scrollIntoView(); else window.scrollTo(0, 0);
      next.focus({ preventScroll: true });
      document.getElementById('navigation-status').textContent = 'Page loaded.';
      document.dispatchEvent(new CustomEvent('spirit:navigated', { detail: { root: next } }));
    } catch (error) { if (error.name !== 'AbortError') location.assign(url.href); }
  }
  document.addEventListener('click', e => {
    const track = e.target.closest('.play-track');
    if (track) {
      if (queue[current] && queue[current].trackId === track.dataset.trackId) { playToggle.click(); return; }
      queue = Array.from(document.querySelectorAll('.play-track'), b => ({ ...b.dataset }));
      play(queue.findIndex(item => item.trackId === track.dataset.trackId)); return;
    }
    const photo = e.target.closest('.photo-open');
    if (photo && !e.defaultPrevented && e.button === 0 && !e.metaKey && !e.ctrlKey && !e.shiftKey && !e.altKey && typeof dialog.showModal === 'function') { e.preventDefault(); photoOpener = photo; const img = document.getElementById('dialog-photo'); img.src = photo.dataset.photo; img.alt = photo.dataset.caption; document.getElementById('dialog-caption').textContent = photo.dataset.caption; dialog.showModal(); return; }
    const link = e.target.closest('a[href]');
    if (!link || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || link.target || link.hasAttribute('download')) return;
    const url = new URL(link.href, location.href);
    if (!internal(url) || (url.pathname === location.pathname && url.search === location.search && url.hash)) return;
    e.preventDefault(); navigate(url);
  });
  document.addEventListener('submit', e => {
    const form = e.target.closest('.public-filter'); if (!form) return;
    const url = new URL(form.action); if (!internal(url)) return;
    e.preventDefault(); url.search = new URLSearchParams(new FormData(form)).toString(); navigate(url);
  });
  paintPlayback();
  window.addEventListener('popstate', () => { const url = new URL(location.href); if (internal(url)) navigate(url, false); else location.reload(); });
})();
