@php
  $apiUrl = url('/api/services');
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4" id="services-cards">
  <div class="col-span-1 text-sm text-gray-500" id="services-loading">Cargando servicios...</div>
</div>

<script>
(function(){
  const container = document.getElementById('services-cards');
  const search = document.getElementById('search');
  const apiUrl = @json($apiUrl);

  function el(tag, cls, text){ const e = document.createElement(tag); if(cls) e.className = cls; if(text) e.textContent = text; return e; }

  async function fetchAll() {
    container.innerHTML = '<div class="col-span-1 text-sm text-gray-500">Cargando servicios...</div>';
    try {
      const res = await fetch(apiUrl, { headers: {'Accept':'application/json'} });
      if (res.status === 200) {
        const list = await res.json();
        renderList(Array.isArray(list) ? list : []);
        return;
      }
      container.innerHTML = '<div class="col-span-1 text-sm text-gray-500">No hay servicios disponibles</div>';
    } catch (err) {
      console.error(err);
      container.innerHTML = '<div class="col-span-1 text-sm text-red-600">Error cargando servicios</div>';
    }
  }

  function renderList(list){
    if (!list || list.length === 0) {
      container.innerHTML = '<div class="col-span-1 text-sm text-gray-500">No hay servicios</div>';
      return;
    }
    container.innerHTML = '';
    list.forEach(s => {
      const card = el('div', 'bg-white p-4 rounded shadow flex');

      const imgWrap = el('div', 'w-24 h-24 mr-4 flex-shrink-0');
      const img = document.createElement('img');
      img.className = 'w-24 h-24 object-cover rounded';
      img.alt = s.name || 'Servicio';
      // normalize URL: if backend returned a relative /storage path, make it absolute
      // fallback to an existing icon if no photo is provided
      function normalizeUrl(u) {
        if (!u) return '/icons/services.svg';
        try {
          // if already absolute (http/https), return as is
          const parsed = new URL(u, window.location.origin);
          return parsed.href;
        } catch (e) {
          return '/icons/services.svg';
        }
      }
      const photoUrl = normalizeUrl(s.photo_url);
      console.debug('service image', s.id, photoUrl);
      img.src = photoUrl;
      img.onerror = function () { this.src = '/icons/default-image.svg'; };
      // ensure onerror also falls back to a known existing icon
      img.addEventListener('error', function () { this.src = '/icons/services.svg'; });
      imgWrap.appendChild(img);

      const body = el('div', 'flex-1');
      const title = el('div', 'font-semibold text-lg mb-1', s.name || '—');
      const desc = el('div', 'text-sm text-gray-600 mb-2', s.description || '');
      const price = el('div', 'text-sm text-gray-700 font-medium mb-2', s.price ? ('$' + Number(s.price).toFixed(2)) : '');

      // action buttons: match employee style (edit anchor + delete button)
      const actions = el('div', 'mt-4 grid grid-cols-2 gap-3');

      const editA = document.createElement('a');
      editA.href = '/dashboard/servicios/create?edit=' + s.id;
      editA.setAttribute('data-nav', '');
      editA.className = 'flex items-center justify-center px-3 py-2 bg-white border border-gray-200 rounded-md text-sm text-gray-700 hover:bg-gray-50 shadow-sm transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-200';
      editA.innerHTML = '<span class="inline-flex w-7 h-7 mr-2 items-center justify-center bg-gray-100 rounded-full"><img src="/icons/edit.svg" alt="Editar" class="w-3 h-3" /></span><span class="font-medium">Editar</span>';

      const delBtn = document.createElement('button');
      delBtn.type = 'button';
      delBtn.className = 'btn-delete flex items-center justify-center px-3 py-2 bg-white border border-red-100 rounded-md text-sm text-red-700 hover:bg-red-50 shadow-sm transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-200';
      delBtn.setAttribute('aria-label', 'Eliminar');
      delBtn.innerHTML = '<span class="inline-flex w-7 h-7 mr-2 items-center justify-center bg-red-100 rounded-full"><img src="/icons/trash.svg" alt="Eliminar" class="w-3 h-3" /></span><span class="font-medium">Eliminar</span>';

      actions.appendChild(editA);
      actions.appendChild(delBtn);

      body.appendChild(title);
      body.appendChild(desc);
      body.appendChild(price);
      body.appendChild(actions);

      card.appendChild(imgWrap);
      card.appendChild(body);

      container.appendChild(card);

      // wire delete with CSRF and confirm
      delBtn.addEventListener('click', async function () {
        const ok = window.confirmDelete ? await window.confirmDelete('¿Eliminar este servicio?') : confirm('¿Eliminar este servicio?');
        if (!ok) return;
        try {
          const token = document.querySelector('input[name="_token"]')?.value;
          const headers = { 'Accept':'application/json' };
          if (token) headers['X-CSRF-TOKEN'] = token;
          const res = await fetch(apiUrl + '/' + s.id, { method: 'DELETE', headers, credentials: 'same-origin' });
          if (res.status === 200) {
            if (window.showToast) showToast('Servicio eliminado', { type: 'success' });
            fetchAll();
            return;
          }
          if (window.showToast) showToast('No se pudo eliminar el servicio', { type: 'error' });
        } catch (err) {
          console.error(err);
          if (window.showToast) showToast('Error de red', { type: 'error' });
        }
      });
    });
  }

  // search handling (debounce)
  let timer = null;
  if (search) {
    search.addEventListener('input', function(){
      clearTimeout(timer);
      timer = setTimeout(async ()=>{
        const q = search.value.trim();
        if (!q) { fetchAll(); return; }
        try {
          const res = await fetch(apiUrl + '/' + encodeURIComponent(q), { headers: {'Accept':'application/json'} });
          if (res.status === 200) {
            const payload = await res.json();
            // getServiceByName returns single object; normalize to array
            const arr = Array.isArray(payload) ? payload : (payload ? [payload] : []);
            renderList(arr);
            return;
          }
          // not found: empty state
          container.innerHTML = '<div class="col-span-1 text-sm text-gray-500">No hay resultados</div>';
        } catch (err) {
          console.error(err);
        }
      }, 350);
    });
  }

  // initial load
  fetchAll();
})();
</script>
