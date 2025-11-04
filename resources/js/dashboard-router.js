// Dashboard router: History API + fetch to navigate /dashboard/* without full page reload
// Intercepts clicks on links that point to /dashboard and replaces #dashboard-content

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
  container.innerHTML = html;
  runInlineScripts(container);
}

function updateActiveSidebar(url) {
  try {
    const path = new URL(url, location.href).pathname.replace(/\/+$/, '');
    document.querySelectorAll('aside a').forEach(a => {
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

    // Encuentra el header (si existe) y calcula espacio disponible
    const header = document.querySelector('header');
    const headerHeight = header ? header.getBoundingClientRect().height : 0;

    // Algunas vistas contienen un <main> interno con padding — restamos el padding aproximado
    const viewportHeight = window.innerHeight;
    const available = Math.max(0, Math.floor(viewportHeight - headerHeight));

    // Aplicar min-height para que el contenedor ocupe el espacio restante
    container.style.minHeight = available + 'px';

    // Si el container contiene un <main> directo, asegurarnos también de ajustar su min-height
    const innerMain = container.querySelector(':scope > main');
    if (innerMain) {
      innerMain.style.minHeight = Math.max(0, available - 16) + 'px';
    }
  } catch (e) {
    // no bloquear si falla
    console.warn('adjustContentHeight failed', e);
  }
}

function runInlineScripts(el) {
  // Ejecuta scripts inyectados con innerHTML (solo scripts sin type=module)
  el.querySelectorAll('script').forEach(oldScript => {
    const script = document.createElement('script');
    // preserve type (important for module scripts injected by Vite)
    if (oldScript.type) script.type = oldScript.type;
    // preserve integrity and crossorigin attributes if present
    if (oldScript.integrity) script.integrity = oldScript.integrity;
    if (oldScript.crossOrigin) script.crossOrigin = oldScript.crossOrigin;

    if (oldScript.src) {
      script.src = oldScript.src;
      // Ensure scripts preserve execution order
      script.async = false;
    } else {
      // For inline module scripts, ensure type is module so `import`/`export` work
      if (!script.type) script.type = 'module';
      script.textContent = oldScript.textContent;
    }

    // Append then remove to execute in document context
    document.head.appendChild(script).parentNode.removeChild(script);
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

    // intentar extraer un <title data-push> desde el fragmento
    const tmp = document.createElement('div');
    tmp.innerHTML = html;
    const titleTag = tmp.querySelector('title[data-push]');
    if (titleTag) document.title = titleTag.textContent;

    if (addToHistory) history.pushState({ url }, '', url);
    container.scrollTop = 0;
    // Update sidebar active link and notify other scripts
    updateActiveSidebar(url);
    // Allow other scripts to reinitialize behaviors after dynamic navigation
    document.dispatchEvent(new CustomEvent('dashboard:navigated', { detail: { url } }));
    // trigger a resize event so components that listen to resize can recalc layout
    window.dispatchEvent(new Event('resize'));
  } catch (err) {
    console.error('Navigation failed', err);
    // Fallback: navegación normal si falla
    window.location.href = url;
  } finally {
    document.body.classList.remove('loading-dashboard');
  }
}

function onDocumentClick(e) {
  const a = e.target.closest('a');
  if (!a) return;
  if (a.target && a.target !== '_self') return; // no interceptar links que abren en nueva pestaña
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
  // Ajuste inicial y al cambiar tamaño
  adjustContentHeight();
  window.addEventListener('resize', adjustContentHeight);
}

// Auto-init when the dashboard container exists
if (document.querySelector(containerSelector)) {
  // If using modules via Vite, this file will be loaded as module and executed
  initDashboardRouter();
}
