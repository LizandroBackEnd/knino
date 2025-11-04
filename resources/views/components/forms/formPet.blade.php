@php
  use App\Models\enums\SpeciesEnum;
  $apiUrl = url('/api/pets');
  $redirectUrl = url('/dashboard/mascotas');
  $breedEndpoint = url('/api/breed'); // we'll append /{species}
  $clientEndpoint = url('/api/clients'); // we'll append /{name}
  $speciesValues = SpeciesEnum::values();
@endphp

<main class="p-6" style="font-family: var(--font-secondary);">
  <div class="relative mb-6">
    <div class="absolute left-0 top-0">
      <a href="{{ $redirectUrl }}" class="inline-flex items-center px-4 py-3 rounded-md text-gray-700" data-nav>
        @if(file_exists(public_path('icons/return.svg')))
          @php
            $ret = file_get_contents(public_path('icons/return.svg'));
            $ret = preg_replace('/\s(width|height|class)="[^"]*"/i', '', $ret);
            $ret = preg_replace('/<path[^>]*d="M0 0h24v24H0z"[^>]*\/>/i', '', $ret);
            $ret = preg_replace('/<svg(.*?)>/i', '<svg$1 preserveAspectRatio="xMidYMid meet">', $ret, 1);
          @endphp
          <span class="inline-flex items-center justify-center w-6 h-6 mr-4 icon-inline" aria-hidden="true">{!! $ret !!}</span>
        @else
          <svg class="w-6 h-6 mr-4 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        @endif
        <span class="font-semibold text-base">Volver</span>
      </a>
    </div>

    <div class="text-center">
      <h1 class="text-3xl font-extrabold mb-1" style="font-family: var(--font-primary); color: var(--text-title);">Agregar Mascota</h1>
      <p class="text-sm text-gray-600">Completa el formulario para registrar una nueva mascota</p>
    </div>
  </div>

  <div class="max-w-3xl bg-white p-6 rounded shadow mx-auto">
    <form id="pet-create-form" class="space-y-4" action="#" method="post" novalidate>
      @csrf

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Nombre <span class="text-red-500">*</span></label>
          <input name="name" type="text" placeholder="Ej: Firulais" class="form-control mt-1 block w-full" required>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Fecha de Nacimiento <span class="text-red-500">*</span></label>
          <input name="birth_date" type="date" class="form-control mt-1 block w-full" required>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Color <span class="text-red-500">*</span></label>
          <input name="color" type="text" placeholder="Ej: Marrón" class="form-control mt-1 block w-full" required>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Especie <span class="text-red-500">*</span></label>
          <select name="species" id="pet-species" class="form-control mt-1 block w-full" required>
            <option value="">Selecciona una especie</option>
            @foreach($speciesValues as $s)
              <option value="{{ $s }}">{{ ucfirst($s) }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Raza <span class="text-red-500">*</span></label>
          <select name="breed_id" id="pet-breed" class="form-control mt-1 block w-full" required>
            <option value="">Seleccione especie primero</option>
          </select>
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700">Cliente <span class="text-red-500">*</span></label>
          <div class="relative">
            <input id="client-search" name="client_search" type="search" placeholder="Escribe nombre del cliente" class="form-control mt-1 block w-full" autocomplete="off">
            <input id="client-id" name="client_id" type="hidden">
            <div id="client-results" class="absolute left-0 right-0 bg-white border mt-1 rounded shadow z-50" style="display:none; max-height:220px; overflow:auto;"></div>
          </div>
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700">Foto (URL) <span class="text-red-500">*</span></label>
          <input name="photo_url" id="photo-url" type="url" placeholder="https://ejemplo.com/imagen.jpg" class="form-control mt-1 block w-full" required>
        </div>
      </div>

      <div class="mt-6">
        <div class="flex justify-center">
          <button type="submit" id="pet-create-submit" class="px-8 py-3 rounded-md text-white btn-green text-lg w-full md:w-1/3">Guardar Mascota</button>
        </div>
      </div>
    </form>
  </div>
</main>

<script>
  (function () {
    const form = document.getElementById('pet-create-form');
    if (!form) return;

    const apiUrl = @json($apiUrl);
    const redirectUrl = @json($redirectUrl);
    const breedEndpoint = @json($breedEndpoint);
    const clientEndpoint = @json($clientEndpoint);

    function clearErrors() {
      form.querySelectorAll('.text-sm.text-red-600').forEach(el => el.remove());
      form.querySelectorAll('[aria-invalid]').forEach(el => el.removeAttribute('aria-invalid'));
    }

    function showErrors(errors) {
      Object.keys(errors).forEach(name => {
        const field = form.querySelector('[name="' + name + '"]');
        if (!field) return;
        field.setAttribute('aria-invalid', 'true');
        const p = document.createElement('p');
        p.className = 'text-sm text-red-600 mt-1';
        p.textContent = Array.isArray(errors[name]) ? errors[name].join(', ') : errors[name];
        field.parentNode.appendChild(p);
      });
    }

    // species -> breeds
    const speciesSelect = document.getElementById('pet-species');
    const breedSelect = document.getElementById('pet-breed');

    speciesSelect.addEventListener('change', async function (e) {
      const val = e.target.value;
      breedSelect.innerHTML = '<option value="">Cargando...</option>';
      if (!val) {
        breedSelect.innerHTML = '<option value="">Seleccione especie primero</option>';
        return;
      }

      try {
        const res = await fetch(breedEndpoint + '/' + encodeURIComponent(val), { headers: { 'Accept': 'application/json' } });
        if (!res.ok) {
          breedSelect.innerHTML = '<option value="">No se encontraron razas</option>';
          return;
        }
        const payload = await res.json();
        // payload may be array of breeds or object; try to handle array
        const list = Array.isArray(payload) ? payload : (payload.data || []);
        if (!list.length) {
          breedSelect.innerHTML = '<option value="">No se encontraron razas</option>';
          return;
        }
        breedSelect.innerHTML = '<option value="">Selecciona una raza</option>';
        list.forEach(b => {
          const opt = document.createElement('option');
          opt.value = b.id ?? b.value ?? b.name ?? '';
          opt.textContent = b.name ?? b.label ?? b.value ?? opt.value;
          breedSelect.appendChild(opt);
        });
      } catch (err) {
        console.error('Error fetching breeds', err);
        breedSelect.innerHTML = '<option value="">Error al cargar razas</option>';
      }
    });

    // client search with debounce
    const clientInput = document.getElementById('client-search');
    const clientResults = document.getElementById('client-results');
    const clientIdInput = document.getElementById('client-id');
    let clientTimer = null;

    clientInput.addEventListener('input', function (e) {
      const q = e.target.value.trim();
      clientIdInput.value = '';
      clientResults.style.display = 'none';
      clientResults.innerHTML = '';
      if (clientTimer) clearTimeout(clientTimer);
      if (q.length < 2) return;
      clientTimer = setTimeout(() => fetchClients(q), 300);
    });

    async function fetchClients(q) {
      try {
        const res = await fetch(clientEndpoint + '/' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } });
        if (!res.ok) {
          // no clients
          return;
        }
        const payload = await res.json();
        // handle array or single object
        const list = Array.isArray(payload) ? payload : (payload.data ? payload.data : (Array.isArray(payload.clients) ? payload.clients : [payload]));
        renderClientResults(list);
      } catch (err) {
        console.error('Error searching clients', err);
      }
    }

    function renderClientResults(list) {
      clientResults.innerHTML = '';
      if (!list || !list.length) {
        clientResults.style.display = 'none';
        return;
      }
      list.forEach(c => {
        const div = document.createElement('div');
        div.className = 'px-3 py-2 hover:bg-gray-100 cursor-pointer';
        div.textContent = (c.name ?? c.nombre ?? '') + (c.last_name_primary ? ' ' + c.last_name_primary : '') + (c.last_name_secondary ? ' ' + c.last_name_secondary : '') + (c.email ? ' — ' + c.email : '');
        div.addEventListener('click', function () {
          clientInput.value = div.textContent;
          clientIdInput.value = c.id ?? c.client_id ?? '';
          clientResults.style.display = 'none';
        });
        clientResults.appendChild(div);
      });
      clientResults.style.display = 'block';
    }

    form.addEventListener('submit', async function (e) {
      e.preventDefault();
      clearErrors();
      const submit = document.getElementById('pet-create-submit');
      submit.disabled = true;
      submit.classList.add('opacity-70');

      const data = {};
      new FormData(form).forEach((v,k) => { if (k !== 'client_search') data[k] = v; });

      const tokenInput = form.querySelector('input[name="_token"]');
      const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
      if (tokenInput) headers['X-CSRF-TOKEN'] = tokenInput.value;

      try {
        const res = await fetch(apiUrl, {
          method: 'POST',
          headers: headers,
          body: JSON.stringify(data),
          credentials: 'same-origin'
        });

        if (res.status === 201 || res.status === 200) {
          showToast('Mascota creada correctamente', { type: 'success' });
          window.location.href = redirectUrl;
          return;
        }

        if (res.status === 422) {
          const payload = await res.json();
          if (payload && payload.errors) showErrors(payload.errors);
          else console.warn('Validation failed but no errors object', payload);
          submit.disabled = false;
          submit.classList.remove('opacity-70');
          return;
        }

        console.error('Unexpected response', res.status);
        showToast('Ocurrió un error al intentar guardar la mascota. Intenta de nuevo.', { type: 'error' });
      } catch (err) {
        console.error('Request failed', err);
        showToast('No se pudo conectar con el servidor.', { type: 'error' });
      } finally {
        submit.disabled = false;
        submit.classList.remove('opacity-70');
      }
    });

  })();
</script>
