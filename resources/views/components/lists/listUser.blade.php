@php
  $apiUrl = url('/api/users');
  $formUrl = url('/dashboard/users/create');
  $csrf = csrf_token();
@endphp

@php
  function load_icon($name, $fallback) {
    $path = public_path('icons/' . $name);
    if (file_exists($path)) {
      $svg = file_get_contents($path);
      $svg = preg_replace('/\s(width|height|class)="[^"]*"/i', '', $svg);
      $svg = preg_replace('/<path[^>]*d="M0 0h24v24H0z"[^>]*\/?>/i', '', $svg);
      $svg = preg_replace('/<svg(.*?)>/i', '<svg$1 preserveAspectRatio="xMidYMid meet">', $svg, 1);
      return $svg;
    }
    return $fallback;
  }

  $mailSvg = load_icon('mail.svg', '<svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m0 8V6a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h14a2 2 0 002-2z"/></svg>');
  $phoneSvg = load_icon('phone.svg', '<svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.6a1 1 0 01.95.68L11 7l-2 2a12 12 0 006 6l2-2 3.32 1.45a1 1 0 01.68.95V19a2 2 0 01-2 2H5a2 2 0 01-2-2V5z"/></svg>');
  $roleSvg = load_icon('employees.svg', '<svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m0-6a4 4 0 11-8 0 4 4 0 018 0z"/></svg>');
@endphp

<div id="users-list" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
  <div class="col-span-2 text-center text-gray-500">Cargando usuarios...</div>
</div>

<script>
  (function () {
    const container = document.getElementById('users-list');
    if (!container) return;

    const apiUrl = @json($apiUrl);
    const editBase = @json($formUrl);
    const csrfToken = @json($csrf);

    const mailIcon = @json($mailSvg);
    const phoneIcon = @json($phoneSvg);
    const roleIcon = @json($roleSvg);

    function el(html) {
      const template = document.createElement('template');
      template.innerHTML = html.trim();
      return template.content.firstChild;
    }

    function formatName(u) {
      return (u.name || '') + ' ' + (u.last_name_primary || '') + (u.last_name_secondary ? ' ' + u.last_name_secondary : '');
    }

    function roleLabel(r) {
      if (!r && r !== 0) return '';
      const s = String(r).toLowerCase();
      if (s === 'admin') return 'Administrador';
      if (s === 'receptionist') return 'Recepción';
      if (s === 'veterinarian') return 'Veterinario';
      return String(r);
    }

    function debounce(fn, delay = 300) {
      let t;
      return function (...args) { clearTimeout(t); t = setTimeout(() => fn.apply(this, args), delay); };
    }

    async function fetchUsers() {
      container.innerHTML = '<div class="col-span-2 text-center text-gray-500">Cargando usuarios...</div>';
      try {
        const res = await fetch(apiUrl, { credentials: 'same-origin' });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const items = await res.json();
        renderUsers(items || []);
      } catch (err) {
        console.error('Fetch users failed', err);
        container.innerHTML = '<div class="col-span-2 text-center text-red-600">No se pudieron cargar los usuarios.</div>';
      }
    }

    function renderUsers(items) {
      container.innerHTML = '';
      if (!items.length) {
        container.innerHTML = '<div class="col-span-2 text-center text-gray-500">No hay usuarios registrados.</div>';
        return;
      }

      items.forEach(u => {
        const roleText = roleLabel(u.role || '');
        const roleBlock = roleText ? (`<div class="mt-1 flex items-center text-sm text-gray-600"><span class="inline-flex w-4 h-4 mr-2" aria-hidden="true">${roleIcon}</span><span>${escapeHtml(roleText)}</span></div>`) : '';

        const card = el(`
          <div class="bg-white rounded shadow p-4">
            <div>
              <div class="text-base font-semibold text-gray-800">${escapeHtml(formatName(u))}</div>
              ${roleBlock}
              <div class="mt-1 flex items-center text-sm text-gray-600"><span class="inline-flex w-4 h-4 mr-2" aria-hidden="true">${mailIcon}</span><span>${escapeHtml(u.email || '')}</span></div>
              <div class="mt-1 flex items-center text-sm text-gray-600"><span class="inline-flex w-4 h-4 mr-2" aria-hidden="true">${phoneIcon}</span><span>${escapeHtml(u.phone || '')}</span></div>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-3">
              <a href="${editBase}?edit=${u.id}" data-nav class="flex items-center justify-center px-3 py-2 bg-white border border-gray-200 rounded-md text-sm text-gray-700 hover:bg-gray-50 shadow-sm transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-200">
                <span class="inline-flex w-7 h-7 mr-2 items-center justify-center bg-gray-100 rounded-full"><img src="/icons/edit.svg" alt="Editar" class="w-3 h-3" /></span>
                <span class="font-medium">Editar</span>
              </a>
              <button type="button" data-user-id="${u.id}" class="btn-delete flex items-center justify-center px-3 py-2 bg-white border border-red-100 rounded-md text-sm text-red-700 hover:bg-red-50 shadow-sm transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-200">
                <span class="inline-flex w-7 h-7 mr-2 items-center justify-center bg-red-100 rounded-full"><img src="/icons/trash.svg" alt="Eliminar" class="w-3 h-3" /></span>
                <span class="font-medium">Eliminar</span>
              </button>
            </div>
          </div>
        `);

        container.appendChild(card);
      });

      container.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', async function () {
          const id = this.getAttribute('data-user-id');
          if (!id) return;
          let ok = true;
          try { if (window.confirmDelete) ok = await window.confirmDelete('¿Eliminar este usuario?'); else ok = confirm('¿Eliminar este usuario?'); } catch (e) { ok = false; }
          if (!ok) return;
          try {
            const res = await fetch(apiUrl + '/' + encodeURIComponent(id), { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }, credentials: 'same-origin' });
            if (res.ok) { if (window.showToast) window.showToast('Usuario eliminado', { type: 'success' }); fetchUsers(); return; }
            const payload = await res.json().catch(() => ({}));
            const msg = payload && payload.message ? payload.message : 'No se pudo eliminar el usuario.';
            if (window.showToast) window.showToast(msg, { type: 'error' }); else alert(msg);
          } catch (err) { console.error('Delete failed', err); if (window.showToast) window.showToast('No se pudo conectar', { type: 'error' }); else alert('No se pudo conectar'); }
        });
      });
    }

    function escapeHtml(unsafe) { if (unsafe === null || unsafe === undefined) return ''; return String(unsafe).replace(/[&<>\"]/g, function (m) { switch (m) { case '&': return '&amp;'; case '<': return '&lt;'; case '>': return '&gt;'; case '"': return '&quot;'; default: return m; } }); }

    // wire search from parent input
    const searchInput = document.getElementById('search');
    if (searchInput) {
      const onSearch = debounce(async function (e) {
        const q = (e.target.value || '').trim();
        if (!q) { fetchUsers(); return; }
        try {
          container.innerHTML = '<div class="col-span-2 text-center text-gray-500">Buscando...</div>';
          const res = await fetch(apiUrl + '/' + encodeURIComponent(q), { credentials: 'same-origin' });
          if (res.status === 404) { container.innerHTML = '<div class="col-span-2 text-center text-gray-500">No hay resultados.</div>'; return; }
          if (!res.ok) throw new Error('HTTP ' + res.status);
          const payload = await res.json();
          if (Array.isArray(payload)) renderUsers(payload); else if (payload && typeof payload === 'object') renderUsers([payload]); else container.innerHTML = '<div class="col-span-2 text-center text-gray-500">No hay resultados.</div>';
        } catch (err) { console.error('Search failed', err); container.innerHTML = '<div class="col-span-2 text-center text-red-600">Error al buscar usuarios.</div>'; }
      }, 300);
      searchInput.addEventListener('input', onSearch);
    }

    fetchUsers();
  })();
</script>
