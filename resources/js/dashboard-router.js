const containerSelector = '#dashboard-content';

function isDashboardLink(link) {
  try {
    if (!link || !link.href) return false;
    const url = new URL(link.href, location.href);
    return url.pathname.startsWith('/dashboard');
  } catch (e) {
    return false;
  }
}

async function fetchFragment(url) {
  // ensure server knows this is an AJAX fragment request by adding a query flag
  try {
    const u = new URL(url, location.href);
    if (!u.searchParams.has('ajax')) u.searchParams.set('ajax', '1');
    url = u.toString();
  } catch (e) {
    // ignore URL parsing errors and use original url
  }

  const res = await fetch(url, {
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'text/html, application/xhtml+xml'
    },
    credentials: 'same-origin'
  });
  if (!res.ok) throw new Error('Network response was not ok: ' + res.status);
  const html = await res.text();
  return html;
}

function setContent(html) {
  const container = document.querySelector(containerSelector);
  if (!container) return;
  // If server accidentally returned a full HTML document, try to extract the inner fragment
  if (/\<html[\s>]/i.test(html)) {
    const tmp = document.createElement('div');
    tmp.innerHTML = html;
    const inner = tmp.querySelector('#dashboard-content');
    if (inner) {
      container.innerHTML = inner.innerHTML;
    } else {
      // fallback: set raw html
      container.innerHTML = html;
    }
  } else {
    container.innerHTML = html;
  }

  return runInlineScripts(container);
}

function updateActiveSidebar(url) {
  try {
    const path = new URL(url, location.href).pathname.replace(/\/+$/, '');
  // only consider links inside the sidebar navigation (avoid footer/logout links)
  document.querySelectorAll('aside nav a').forEach(a => {
      try {
        const aPath = new URL(a.href, location.href).pathname.replace(/\/+$/, '');
        const img = a.querySelector('img');
        const isActive = aPath === path || (aPath === '/dashboard' && path === '/dashboard');

        if (isActive) {
          a.classList.add('bg-[var(--color-primary)]', 'text-white');
          a.classList.remove('text-gray-700', 'hover:bg-gray-50');
          if (img) img.classList.add('filter', 'brightness-0', 'invert');
        } else {
          a.classList.remove('bg-[var(--color-primary)]', 'text-white');
          a.classList.add('text-gray-700', 'hover:bg-gray-50');
          if (img) img.classList.remove('filter', 'brightness-0', 'invert');
        }
      } catch (e) {
        // ignore per-link errors
      }
    });
  } catch (e) {
    console.warn('updateActiveSidebar failed', e);
  }
}

function adjustContentHeight() {
  try {
    const container = document.querySelector(containerSelector);
    if (!container) return;

    const header = document.querySelector('header');
    const headerHeight = header ? header.getBoundingClientRect().height : 0;

    const viewportHeight = window.innerHeight;
    const available = Math.max(0, Math.floor(viewportHeight - headerHeight));

    container.style.minHeight = available + 'px';

    const innerMain = container.querySelector(':scope > main');
    if (innerMain) {
      innerMain.style.minHeight = Math.max(0, available - 16) + 'px';
    }
  } catch (e) {
    console.warn('adjustContentHeight failed', e);
  }
}

function runInlineScripts(el) {
  const scripts = Array.from(el.querySelectorAll('script'));
  return Promise.all(scripts.map(oldScript => new Promise((resolve) => {
    const script = document.createElement('script');
    if (oldScript.type) script.type = oldScript.type;
    if (oldScript.integrity) script.integrity = oldScript.integrity;
    if (oldScript.crossOrigin) script.crossOrigin = oldScript.crossOrigin;

    if (oldScript.src) {
      // external script: append and remove after load/error
      script.src = oldScript.src;
      script.async = false;
      script.addEventListener('load', () => {
        // clean up inserted script
        script.parentNode && script.parentNode.removeChild(script);
        resolve();
      }, { once: true });
      script.addEventListener('error', () => {
        script.parentNode && script.parentNode.removeChild(script);
        resolve();
      }, { once: true });
      document.head.appendChild(script);
    } else {
      // inline script: execute synchronously by appending
      if (!script.type) script.type = 'module';
      script.textContent = oldScript.textContent;
      document.head.appendChild(script);
      // remove immediately after execution
      script.parentNode && script.parentNode.removeChild(script);
      resolve();
    }

    // remove the original script from the fragment to avoid duplication
    oldScript.parentNode && oldScript.parentNode.removeChild(oldScript);
  })));
}

function waitForImagesAndAdjust(container) {
  return new Promise((resolve) => {
    try {
      if (!container) {
        adjustContentHeight();
        return resolve();
      }
      const imgs = Array.from(container.querySelectorAll('img'));
      if (imgs.length === 0) {
        adjustContentHeight();
        return resolve();
      }

      const pending = imgs.filter(i => !i.complete);
      if (pending.length === 0) {
        // all already loaded
        adjustContentHeight();
        return resolve();
      }

      const loaders = pending.map(img => new Promise(res => {
        img.addEventListener('load', res, { once: true });
        img.addEventListener('error', res, { once: true });
      }));

      // wait for images or timeout (500ms) to avoid hanging
      Promise.race([Promise.all(loaders), new Promise(res => setTimeout(res, 500))]).then(() => {
        adjustContentHeight();
        // small retries in case of late layout shifts
        setTimeout(adjustContentHeight, 60);
        setTimeout(adjustContentHeight, 300);
        resolve();
      }).catch(() => {
        adjustContentHeight();
        resolve();
      });
    } catch (e) {
      adjustContentHeight();
      resolve();
    }
  });
}

async function navigateTo(url, addToHistory = true) {
  const container = document.querySelector(containerSelector);
  if (!container) {
    window.location.href = url;
    return;
  }

  document.body.classList.add('loading-dashboard');
  try {
    const html = await fetchFragment(url);
    setContent(html);

    const tmp = document.createElement('div');
    tmp.innerHTML = html;
    const titleTag = tmp.querySelector('title[data-push]');
    if (titleTag) document.title = titleTag.textContent;

    if (addToHistory) history.pushState({ url }, '', url);
    container.scrollTop = 0;
    updateActiveSidebar(url);

    // give browser a frame to paint, then adjust heights
    requestAnimationFrame(() => adjustContentHeight());
    // wait for images and adjustments to finish before signaling navigation complete
    await waitForImagesAndAdjust(container);

    // dispatch navigation events after layout has settled so listeners can initialize safely
    document.dispatchEvent(new CustomEvent('dashboard:navigated', { detail: { url } }));
    document.dispatchEvent(new CustomEvent('dashboard:ready', { detail: { url } }));

    // call optional global initializer if present
    if (typeof window.initPage === 'function') {
      try { window.initPage(); } catch (e) { console.warn('window.initPage failed', e); }
    }

    // keep compatibility: signal a resize event for any listeners
    window.dispatchEvent(new Event('resize'));
  } catch (err) {
    console.error('Navigation failed', err);
    window.location.href = url;
  } finally {
    document.body.classList.remove('loading-dashboard');
  }
}

function onDocumentClick(e) {
  const a = e.target.closest('a');
  if (!a) return;
  if (a.target && a.target !== '_self') return; 
  if (!isDashboardLink(a)) return;
  if (a.hasAttribute('download')) return;

  e.preventDefault();
  navigateTo(a.href, true);
}

function onPopState(e) {
  const state = e.state;
  const url = (state && state.url) || location.href;
  navigateTo(url, false);
}

export function initDashboardRouter() {
  document.addEventListener('click', onDocumentClick);
  window.addEventListener('popstate', onPopState);
  history.replaceState({ url: location.href }, '', location.href);
  adjustContentHeight();
  window.addEventListener('resize', adjustContentHeight);
}

if (document.querySelector(containerSelector)) {
  initDashboardRouter();
}
