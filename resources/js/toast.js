(function () {
  const containerId = 'global-toast-container';

  function ensureContainer() {
    let c = document.getElementById(containerId);
    if (c) return c;
    c = document.createElement('div');
    c.id = containerId;
    c.style.position = 'fixed';
    c.style.right = '1rem';
    c.style.bottom = '1rem';
    c.style.zIndex = 9999;
    c.style.display = 'flex';
    c.style.flexDirection = 'column';
    c.style.gap = '0.5rem';
    c.style.alignItems = 'flex-end';
    document.body.appendChild(c);
    return c;
  }

  function createToastElement(message, type) {
    const el = document.createElement('div');
    el.className = 'toast-item';
    el.style.minWidth = '220px';
    el.style.maxWidth = '420px';
    el.style.padding = '12px 16px';
    el.style.borderRadius = '8px';
    el.style.boxShadow = '0 6px 18px rgba(0,0,0,0.12)';
    el.style.color = '#0f172a';
    el.style.fontSize = '14px';
    el.style.transition = 'transform 220ms ease, opacity 220ms ease';
    el.style.transform = 'translateY(8px)';
    el.style.opacity = '0';
    el.style.background = '#ffffff';
    el.style.borderLeft = '4px solid transparent';
    el.style.padding = '12px 16px 12px 12px';

    switch (type) {
      case 'success':
        el.style.borderLeftColor = '#16a34a';
        break;
      case 'error':
        el.style.borderLeftColor = '#dc2626';
        break;
      case 'info':
      default:
        el.style.borderLeftColor = '#0ea5e9';
        break;
    }

    el.textContent = message;
    return el;
  }

  function showToast(message, opts = {}) {
    const { type = 'info', duration = 4000 } = opts;
    const container = ensureContainer();
    const el = createToastElement(message, type);
    container.appendChild(el);

    requestAnimationFrame(() => {
      el.style.transform = 'translateY(0)';
      el.style.opacity = '1';
    });

    const timeout = setTimeout(() => {
      hide();
    }, duration);

    function hide() {
      clearTimeout(timeout);
      el.style.transform = 'translateY(8px)';
      el.style.opacity = '0';
      el.addEventListener('transitionend', () => {
        el.remove();
      }, { once: true });
    }

    el.addEventListener('click', hide);

    return {
      dismiss: hide
    };
  }

  window.showToast = showToast;

})();
