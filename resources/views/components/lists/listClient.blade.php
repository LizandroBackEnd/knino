@php
  $apiUrl = url('/api/clients');
  $formUrl = url('/dashboard/clientes/create');
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
  $pinSvg = load_icon('map-pin.svg', '<svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11a3 3 0 100-6 3 3 0 000 6z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22s8-4.5 8-11a8 8 0 10-16 0c0 6.5 8 11 8 11z"/></svg>');
@endphp

<div id="clients-list" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
  <div class="col-span-2 text-center text-gray-500">Cargando clientes...</div>
</div>

<script>
  (function () {
    const container = document.getElementById('clients-list');
    if (!container) return;

    const apiUrl = @json($apiUrl);
    const editBase = @json($formUrl);
    const csrfToken = @json($csrf);

    const mailIcon = @json($mailSvg);
    const phoneIcon = @json($phoneSvg);
    const pinIcon = @json($pinSvg);

    function el(html) {
      const template = document.createElement('template');
      template.innerHTML = html.trim();
      return template.content.firstChild;
    }

    function formatName(client) {
      return (client.name || '') + ' ' + (client.last_name_primary || '') + (client.last_name_secondary ? ' ' + client.last_name_secondary : '');
    }

    function debounce(fn, delay = 300) {
      let t;
      return function (...args) {
        clearTimeout(t);
        t = setTimeout(() => fn.apply(this, args), delay);
      };
    }

    const searchInput = document.getElementById('search');
    if (searchInput) {
      const onSearch = debounce(async function (e) {
        const q = (e.target.value || '').trim();
        if (!q) {
          // empty -> load all
          fetchClients();
          return;
        }
        try {
          container.innerHTML = '<div class="col-span-2 text-center text-gray-500">Buscando...</div>';
          let res;
          if (q.includes('@')) {
            res = await fetch(apiUrl + '/email/' + encodeURIComponent(q), { credentials: 'same-origin' });
            if (res.status === 404) {
              container.innerHTML = '<div class="col-span-2 text-center text-gray-500">No hay resultados.</div>';
              return;
            }
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const payload = await res.json();
            renderClients(payload ? [payload] : []);
          } else {
            res = await fetch(apiUrl + '/search/' + encodeURIComponent(q), { credentials: 'same-origin' });
            if (res.status === 404) {
              container.innerHTML = '<div class="col-span-2 text-center text-gray-500">No hay resultados.</div>';
              return;
            }
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const payload = await res.json();
            if (Array.isArray(payload)) {
              renderClients(payload);
            } else if (payload && typeof payload === 'object') {
              renderClients([payload]);
            } else {
              container.innerHTML = '<div class="col-span-2 text-center text-gray-500">No hay resultados.</div>';
            }
          }
        } catch (err) {
          console.error('Search failed', err);
          container.innerHTML = '<div class="col-span-2 text-center text-red-600">Error al buscar clientes.</div>';
        }
      }, 300);

      searchInput.addEventListener('input', onSearch);
    }

    async function fetchClients() {
      container.innerHTML = '<div class="text-center text-gray-500">Cargando clientes...</div>';
      try {
        const res = await fetch(apiUrl, { credentials: 'same-origin' });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const clients = await res.json();
        renderClients(clients || []);
      } catch (err) {
        console.error('Failed to load clients', err);
          container.innerHTML = '<div class="col-span-2 text-center text-red-600">No se pudieron cargar los clientes.</div>';
      }
    }

    function renderClients(clients) {
      container.innerHTML = '';
      if (!clients.length) {
        container.innerHTML = '<div class="col-span-2 text-center text-gray-500">No hay clientes registrados.</div>';
        return;
      }

      clients.forEach(client => {
        const card = el(`
          <div class="bg-white rounded shadow p-4">
            <div>
              <div class="text-base font-semibold text-gray-800">${escapeHtml(formatName(client))}</div>
              <div class="mt-1 flex items-center text-sm text-gray-600">
                <span class="inline-flex w-4 h-4 mr-2" aria-hidden="true">${mailIcon}</span>
                <span>${escapeHtml(client.email || '')}</span>
              </div>
              <div class="mt-1 flex items-center text-sm text-gray-600">
                <span class="inline-flex w-4 h-4 mr-2" aria-hidden="true">${phoneIcon}</span>
                <span>${escapeHtml(client.phone || '')}</span>
              </div>
              <div class="mt-1 flex items-center text-sm text-gray-600">
                <span class="inline-flex w-4 h-4 mr-2" aria-hidden="true">${pinIcon}</span>
                <span>${escapeHtml(client.address || '')}</span>
              </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-3">
              <a href="${editBase}?edit=${client.id}" data-nav class="flex items-center justify-center px-3 py-2 bg-white border border-gray-200 rounded-md text-sm text-gray-700 hover:bg-gray-50 shadow-sm transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-200">
                <span class="inline-flex w-7 h-7 mr-2 items-center justify-center bg-gray-100 rounded-full">
                  <img src="/icons/edit.svg" alt="Editar" class="w-3 h-3" />
                </span>
                <span class="font-medium">Editar</span>
              </a>
              <button type="button" data-client-id="${client.id}" class="btn-delete flex items-center justify-center px-3 py-2 bg-white border border-red-100 rounded-md text-sm text-red-700 hover:bg-red-50 shadow-sm transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-200">
                <span class="inline-flex w-7 h-7 mr-2 items-center justify-center bg-red-100 rounded-full">
                  <img src="/icons/trash.svg" alt="Eliminar" class="w-3 h-3" />
                </span>
                <span class="font-medium">Eliminar</span>
              </button>
            </div>
          </div>
        `);

        container.appendChild(card);
      });

      container.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', async function (e) {
          const id = this.getAttribute('data-client-id');
          if (!id) return;
          let ok = true;
          try {
            if (window.confirmDelete) {
              ok = await window.confirmDelete('¿Eliminar este cliente? Esta acción no se puede deshacer.');
            } else {
              ok = confirm('¿Eliminar este cliente? Esta acción no se puede deshacer.');
            }
          } catch (err) {
            console.warn('confirmDelete failed', err);
            ok = false;
          }
          if (!ok) return;

          try {
            const res = await fetch(apiUrl + '/' + encodeURIComponent(id), {
              method: 'DELETE',
              headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
              },
              credentials: 'same-origin'
            });
            if (res.ok) {
              if (window.showToast) window.showToast('Cliente eliminado', { type: 'success' });
              fetchClients();
              return;
            }
            const payload = await res.json().catch(() => ({}));
            const msg = payload && payload.message ? payload.message : 'No se pudo eliminar el cliente.';
            if (window.showToast) window.showToast(msg, { type: 'error' }); else alert(msg);
          } catch (err) {
            console.error('Delete failed', err);
            if (window.showToast) window.showToast('No se pudo conectar con el servidor.', { type: 'error' }); else alert('No se pudo conectar con el servidor.');
          }
        });
      });
    }

    function escapeHtml(unsafe) {
      if (unsafe === null || unsafe === undefined) return '';
      return String(unsafe).replace(/[&<>"]/g, function (m) {
        switch (m) {
          case '&': return '&amp;';
          case '<': return '&lt;';
          case '>': return '&gt;';
          case '"': return '&quot;';
          default: return m;
        }
      });
    }

    fetchClients();
  })();
</script>
