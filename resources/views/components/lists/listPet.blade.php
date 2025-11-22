@php
  $apiUrl = url('/api/pets');
  $formUrl = url('/dashboard/mascotas/create');
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

  $editIcon = load_icon('edit.svg', '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h6M11 12h6M11 19h6M4 6h.01M4 12h.01M4 18h.01"/></svg>');
  $trashIcon = load_icon('trash.svg', '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/></svg>');
@endphp

<div id="pets-list" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
  <div class="col-span-2 text-center text-gray-500">Cargando mascotas...</div>
</div>

<script>
  (function () {
    const container = document.getElementById('pets-list');
    if (!container) return;

    const apiUrl = @json($apiUrl);
    const editBase = @json($formUrl);
    const csrfToken = @json($csrf);

    function el(html) {
      const template = document.createElement('template');
      template.innerHTML = html.trim();
      return template.content.firstChild;
    }

    function escapeHtml(unsafe) { if (unsafe === null || unsafe === undefined) return ''; return String(unsafe).replace(/[&<>\"]/g, function (m) { switch (m) { case '&': return '&amp;'; case '<': return '&lt;'; case '>': return '&gt;'; case '"': return '&quot;'; default: return m; } }); }

    async function fetchPets() {
      container.innerHTML = '<div class="col-span-2 text-center text-gray-500">Cargando mascotas...</div>';
      try {
      const res = await fetch(apiUrl, { credentials: 'same-origin' });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const items = await res.json();
        renderPets(items || []);
      } catch (err) {
        console.error('Fetch pets failed', err);
        container.innerHTML = '<div class="col-span-2 text-center text-red-600">No se pudieron cargar las mascotas.</div>';
      }
    }

    function renderPets(items) {
      container.innerHTML = '';
      if (!items.length) {
        container.innerHTML = '<div class="col-span-2 text-center text-gray-500">No hay mascotas registradas.</div>';
        return;
      }

      items.forEach(pet => {
        const imgSrc = pet.photo_url || '/icons/pets.svg';
        const breed = pet.breed ? pet.breed.name : '';
        const client = pet.client ? ((pet.client.name||'') + (pet.client.last_name_primary ? ' ' + pet.client.last_name_primary : '')) : '';
        const card = el(`
          <div class="bg-white rounded shadow p-4">
            <div class="flex items-start">
              <div class="w-20 h-20 mr-4 flex-shrink-0 bg-gray-100 rounded overflow-hidden">
                <img src="${escapeHtml(imgSrc)}" alt="${escapeHtml(pet.name||'foto')}" class="w-full h-full object-cover" onerror="this.src='/icons/pets.svg'" />
              </div>
              <div class="flex-1">
                <div class="text-base font-semibold text-gray-800">${escapeHtml(pet.name||'')}</div>
                <div class="mt-1 text-sm text-gray-600">
                  <div class="flex items-center space-x-4 flex-wrap">
                    <div class="flex items-center text-sm text-gray-600">
                      <img src="/icons/pets.svg" alt="Tipo" class="w-4 h-4 mr-2" onerror="this.style.display='none'" />
                      <span>${escapeHtml(pet.species||'')}</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                      <img src="/icons/specie.svg" alt="Sexo" class="w-4 h-4 mr-2" onerror="this.style.display='none'" />
                      <span>${escapeHtml(pet.sex||'')}</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                      <img src="/icons/breed.svg" alt="Raza" class="w-4 h-4 mr-2" onerror="this.src='/icons/specie.svg'" />
                      <span>${escapeHtml(breed)}</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                      <img src="/icons/size.svg" alt="Tamaño" class="w-4 h-4 mr-2" onerror="this.style.display='none'" />
                      <span>${escapeHtml(pet.size || '')}</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                      <img src="/icons/weight.svg" alt="Peso" class="w-4 h-4 mr-2" onerror="this.style.display='none'" />
                      <span>${pet.weight !== null && pet.weight !== undefined && pet.weight !== '' ? escapeHtml(Number(pet.weight).toFixed(2) + ' kg') : ''}</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-600 ml-auto">
                      <img src="/icons/clients.svg" alt="Cliente" class="w-4 h-4 mr-2" onerror="this.style.display='none'" />
                      <span>${escapeHtml(client)}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-3">
              <a href="${editBase}?edit=${pet.id}" data-nav class="flex items-center justify-center px-3 py-2 bg-white border border-gray-200 rounded-md text-sm text-gray-700 hover:bg-gray-50 shadow-sm transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-200">
                <span class="inline-flex w-7 h-7 mr-2 items-center justify-center bg-gray-100 rounded-full"><img src="/icons/edit.svg" alt="Editar" class="w-3 h-3" /></span>
                <span class="font-medium">Editar</span>
              </a>
              <button type="button" data-pet-id="${pet.id}" class="btn-delete flex items-center justify-center px-3 py-2 bg-white border border-red-100 rounded-md text-sm text-red-700 hover:bg-red-50 shadow-sm transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-200">
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
          const id = this.getAttribute('data-pet-id');
          if (!id) return;
          let ok = true;
          try { if (window.confirmDelete) ok = await window.confirmDelete('¿Eliminar esta mascota?'); else ok = confirm('¿Eliminar esta mascota?'); } catch (e) { ok = false; }
          if (!ok) return;
          try {
            const res = await fetch(apiUrl + '/' + encodeURIComponent(id), { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }, credentials: 'same-origin' });
            if (res.ok) { if (window.showToast) window.showToast('Mascota eliminada', { type: 'success' }); fetchPets(); return; }
            const payload = await res.json().catch(() => ({}));
            const msg = payload && payload.message ? payload.message : 'No se pudo eliminar la mascota.';
            if (window.showToast) window.showToast(msg, { type: 'error' }); else alert(msg);
          } catch (err) { console.error('Delete failed', err); if (window.showToast) window.showToast('No se pudo conectar', { type: 'error' }); else alert('No se pudo conectar'); }
        });
      });
    }

    const searchInput = document.getElementById('search');
    if (searchInput) {
      let t;
      searchInput.addEventListener('input', function (e) {
        clearTimeout(t);
        t = setTimeout(async () => {
          const q = (e.target.value || '').trim();
          if (!q) { fetchPets(); return; }
          try {
            container.innerHTML = '<div class="col-span-2 text-center text-gray-500">Buscando...</div>';
            const res = await fetch(apiUrl + '/' + encodeURIComponent(q), { credentials: 'same-origin' });
            if (res.status === 404) { container.innerHTML = '<div class="col-span-2 text-center text-gray-500">No hay resultados.</div>'; return; }
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const payload = await res.json();
            if (Array.isArray(payload)) renderPets(payload); else if (payload && typeof payload === 'object') renderPets([payload]); else container.innerHTML = '<div class="col-span-2 text-center text-gray-500">No hay resultados.</div>';
          } catch (err) { console.error('Search failed', err); container.innerHTML = '<div class="col-span-2 text-center text-red-600">Error al buscar mascotas.</div>'; }
        }, 300);
      });
    }

    fetchPets();
  })();
</script>
