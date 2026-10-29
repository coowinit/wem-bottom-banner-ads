(() => {
  'use strict';
  const banner = document.querySelector('.wem-bba-banner');
  if (!banner) return;
  const close = banner.querySelector('.wem-bba-banner__close');
  const version = banner.dataset.bannerVersion || '1';
  const key = 'wem_bba_closed_' + version;
  try {
    if (window.sessionStorage.getItem(key) === '1') {
      banner.hidden = true;
      return;
    }
  } catch (error) { /* Private-browsing/storage restrictions: continue normally. */ }
  if (close) close.addEventListener('click', () => {
    banner.hidden = true;
    try { window.sessionStorage.setItem(key, '1'); } catch (error) { /* No persistent session available. */ }
  });
})();
