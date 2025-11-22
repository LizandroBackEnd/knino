export default function confirmDelete(message = '¿Estás seguro?', opts = {}) {
  const title = opts.title ?? 'Confirmar eliminación';
  const confirmText = opts.confirmText ?? 'Eliminar';
  return new Promise((resolve) => {
    const existing = document.getElementById('confirm-delete-modal');
    if (existing) existing.remove();

    const wrapper = document.createElement('div');
    wrapper.id = 'confirm-delete-modal';
    wrapper.style = 'position:fixed;inset:0;display:flex;align-items:center;justify-content:center;z-index:9999;';
    wrapper.innerHTML = `
      <div style="position:absolute;inset:0;background:rgba(0,0,0,0.4);"></div>
      <div role="dialog" aria-modal="true" style="background:white;border-radius:8px;padding:18px;max-width:420px;width:90%;box-shadow:0 10px 30px rgba(0,0,0,0.2);z-index:10000;">
        <div style="font-weight:600;font-size:16px;margin-bottom:8px;color:#111">${escapeHtml(title)}</div>
        <div style="font-size:14px;color:#444;margin-bottom:16px;">${escapeHtml(message)}</div>
        <div style="display:flex;gap:8px;justify-content:flex-end">
          <button id="confirm-delete-cancel" style="padding:8px 12px;border-radius:6px;border:1px solid #e5e7eb;background:#fff;">Cancelar</button>
          <button id="confirm-delete-ok" style="padding:8px 12px;border-radius:6px;border:0;background:#dc2626;color:#fff;">${escapeHtml(confirmText)}</button>
        </div>
      </div>
    `;

    document.body.appendChild(wrapper);

    const cleanup = (val) => {
      resolve(!!val);
      const el = document.getElementById('confirm-delete-modal');
      if (el) el.remove();
    };

    document.getElementById('confirm-delete-cancel').addEventListener('click', () => cleanup(false));
    document.getElementById('confirm-delete-ok').addEventListener('click', () => cleanup(true));

    wrapper.addEventListener('click', (e) => {
      if (e.target === wrapper) cleanup(false);
    });

    const onKey = (e) => {
      if (e.key === 'Escape') cleanup(false);
    };
    document.addEventListener('keydown', onKey, { once: true });
  });
}

function escapeHtml(unsafe) {
  if (unsafe === null || unsafe === undefined) return '';
  return String(unsafe).replace(/[&<>\"]/g, function (m) {
    switch (m) {
      case '&': return '&amp;';
      case '<': return '&lt;';
      case '>': return '&gt;';
      case '"': return '&quot;';
      default: return m;
    }
  });
}

if (typeof window !== 'undefined') {
  window.confirmDelete = (msg, opts) => confirmDelete(msg, opts);
}
