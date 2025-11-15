@php
  $apiUrl = url('/api/employees');
  $formUrl = url('/dashboard/empleados/create');
  $csrf = csrf_token();
@endphp

@php
  function load_icon($name, $fallback) {
    $path = public_path('icons/' . $name);
    if (file_exists($path)) {
      $svg = file_get_contents($path);
      $svg = preg_replace('/\s(width|height|class)="[^"]*"/i', '', $svg);
      $svg = preg_replace('/<path[^>]*d="M0 0h24v24H0z"[^>]*\/>/i', '', $svg);
      $svg = preg_replace('/<svg(.*?)>/i', '<svg$1 preserveAspectRatio="xMidYMid meet">', $svg, 1);
      return $svg;
    }
    return $fallback;
  }

  $mailSvg = load_icon('mail.svg', '<svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m0 8V6a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h14a2 2 0 002-2z"/></svg>');
  $phoneSvg = load_icon('phone.svg', '<svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.6a1 1 0 01.95.68L11 7l-2 2a12 12 0 006 6l2-2 3.32 1.45a1 1 0 01.68.95V19a2 2 0 01-2 2H5a2 2 0 01-2-2V5z"/></svg>');
  $roleSvg = load_icon('employees.svg', '<svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m0-6a4 4 0 11-8 0 4 4 0 018 0z"/></svg>');
@endphp

<div id="employees-list" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
  <div class="col-span-2 text-center text-gray-500">Cargando empleados...</div>
</div>

<script>
  (function () {
    const container = document.getElementById('employees-list');
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

    function formatName(emp) {
      return (emp.name || '') + ' ' + (emp.last_name_primary || '') + (emp.last_name_secondary ? ' ' + emp.last_name_secondary : '');
    }

    function debounce(fn, delay = 300) {
      let t;
      return function (...args) { clearTimeout(t); t = setTimeout(() => fn.apply(this, args), delay); };
    }

    async function fetchEmployees() {
      container.innerHTML = '<div class="col-span-2 text-center text-gray-500">Cargando empleados...</div>';
  try {
  const res = await fetch(apiUrl, { credentials: 'same-origin' });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    const items = await res.json();
        renderEmployees(items || []);
      } catch (err) {
        console.error('Fetch employees failed', err);
        container.innerHTML = '<div class="col-span-2 text-center text-red-600">No se pudieron cargar los empleados.</div>';
      }
    }

    function renderEmployees(items) {
      container.innerHTML = '';
      if (!items.length) {
        container.innerHTML = '<div class="col-span-2 text-center text-gray-500">No hay empleados registrados.</div>';
        return;
      }

      items.forEach(emp => {
        const card = el(`
          <div class="bg-white rounded shadow p-4">
            <div>
              <div class="text-base font-semibold text-gray-800">${escapeHtml(formatName(emp))}</div>
              <div class="mt-1 flex items-center text-sm text-gray-600"><span class="inline-flex w-4 h-4 mr-2" aria-hidden="true">${roleIcon}</span><span>${escapeHtml(emp.role || '')}</span></div>
              <div class="mt-1 flex items-center text-sm text-gray-600"><span class="inline-flex w-4 h-4 mr-2" aria-hidden="true">${mailIcon}</span><span>${escapeHtml(emp.email || '')}</span></div>
              <div class="mt-1 flex items-center text-sm text-gray-600"><span class="inline-flex w-4 h-4 mr-2" aria-hidden="true">${phoneIcon}</span><span>${escapeHtml(emp.phone || '')}</span></div>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-3">
              <a href="${editBase}?edit=${emp.id}" data-nav class="flex items-center justify-center px-3 py-2 bg-white border border-gray-200 rounded-md text-sm text-gray-700 hover:bg-gray-50 shadow-sm transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-200">
                <span class="inline-flex w-7 h-7 mr-2 items-center justify-center bg-gray-100 rounded-full"><img src="/icons/edit.svg" alt="Editar" class="w-3 h-3" /></span>
                <span class="font-medium">Editar</span>
              </a>
              <button type="button" data-emp-id="${emp.id}" class="btn-delete flex items-center justify-center px-3 py-2 bg-white border border-red-100 rounded-md text-sm text-red-700 hover:bg-red-50 shadow-sm transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-200">
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
          const id = this.getAttribute('data-emp-id');
          if (!id) return;
          let ok = true;
          try { if (window.confirmDelete) ok = await window.confirmDelete('¿Eliminar este empleado?'); else ok = confirm('¿Eliminar este empleado?'); } catch (e) { ok = false; }
          if (!ok) return;
          try {
            const res = await fetch(apiUrl + '/' + encodeURIComponent(id), { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }, credentials: 'same-origin' });
            if (res.ok) { if (window.showToast) window.showToast('Empleado eliminado', { type: 'success' }); fetchEmployees(); return; }
            const payload = await res.json().catch(() => ({}));
            const msg = payload && payload.message ? payload.message : 'No se pudo eliminar el empleado.';
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
        if (!q) { fetchEmployees(); return; }
        try {
          container.innerHTML = '<div class="col-span-2 text-center text-gray-500">Buscando...</div>';
          const res = await fetch(apiUrl + '/' + encodeURIComponent(q), { credentials: 'same-origin' });
          if (res.status === 404) { container.innerHTML = '<div class="col-span-2 text-center text-gray-500">No hay resultados.</div>'; return; }
          if (!res.ok) throw new Error('HTTP ' + res.status);
          const payload = await res.json();
          if (Array.isArray(payload)) renderEmployees(payload); else if (payload && typeof payload === 'object') renderEmployees([payload]); else container.innerHTML = '<div class="col-span-2 text-center text-gray-500">No hay resultados.</div>';
        } catch (err) { console.error('Search failed', err); container.innerHTML = '<div class="col-span-2 text-center text-red-600">Error al buscar empleados.</div>'; }
      }, 300);
      searchInput.addEventListener('input', onSearch);
    }

    fetchEmployees();
  })();
</script>
