@php
  use App\Models\enums\SpeciesEnum;
  $apiUrl = url('/api/pets');
  $redirectUrl = url('/dashboard/mascotas');
  $breedEndpoint = url('/api/breed'); 
  $clientEndpoint = url('/api/clients/search'); 
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
    <form id="pet-create-form" class="space-y-4" action="#" method="post" enctype="multipart/form-data" novalidate>
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
          <label class="block text-sm font-medium text-gray-700">Sexo <span class="text-red-500">*</span></label>
          <select name="sex" id="pet-sex" class="form-control mt-1 block w-full" required>
            <option value="">Selecciona un sexo</option>
            @foreach(App\Models\enums\SexEnum::values() as $sx)
              <option value="{{ $sx }}">{{ ucfirst($sx) }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Raza <span class="text-red-500">*</span></label>
          <div class="flex items-center space-x-2">
            <select name="breed_id" id="pet-breed" class="form-control mt-1 block w-full" required>
              <option value="">Seleccione especie primero</option>
            </select>
            <button type="button" id="btn-add-breed" class="mt-1 inline-flex items-center px-3 py-2 bg-white border border-gray-200 rounded-md text-sm text-gray-700 hover:bg-gray-50 shadow-sm transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-200">
              @if(file_exists(public_path('icons/plus.svg')))
                @php
                  $plus = file_get_contents(public_path('icons/plus.svg'));
                  $plus = preg_replace('/\s(width|height|class)="[^"]*"/i', '', $plus);
                @endphp
                <span class="inline-flex items-center justify-center w-5 h-5 mr-2 icon-inline" aria-hidden="true">{!! $plus !!}</span>
              @else
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
              @endif
              <span>Raza</span>
            </button>
          </div>
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
          <label class="block text-sm font-medium text-gray-700">Foto (archivo) <span class="text-red-500">*</span></label>
          <input name="photo" id="photo-input" type="file" accept="image/*" class="form-control mt-1 block w-full">
          <div id="photo-preview" class="mt-2"></div>
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

<div id="modal-add-breed" data-breed-endpoint="{{ $breedEndpoint }}" class="fixed inset-0 hidden items-center justify-center z-50 backdrop-blur-sm bg-white/10">
  <div class="bg-white rounded-lg shadow max-w-3xl w-full p-6">
    <h3 class="text-lg font-semibold mb-2">Agregar Raza</h3>
    <p class="text-sm text-gray-600 mb-4">Agrega una nueva raza para la especie seleccionada.</p>
    <div>
      <label class="block text-sm font-medium text-gray-700">Especie</label>
      <select id="modal-breed-species" class="form-control mt-1 block w-full" disabled>
        <option value="">--</option>
        @foreach($speciesValues as $s)
          <option value="{{ $s }}">{{ ucfirst($s) }}</option>
        @endforeach
      </select>
    </div>
    <div class="mt-3">
      <label class="block text-sm font-medium text-gray-700">Nombre de la raza</label>
      <input id="modal-breed-name" type="text" class="form-control mt-1 block w-full" placeholder="Ej: Golden Retriever">
  <p id="modal-breed-error" class="text-sm text-red-600 mt-1" style="display:none;"></p>
    </div>
    <div class="mt-4 flex justify-end space-x-2">
  <button type="button" id="modal-breed-cancel" class="px-6 py-3 rounded-md bg-white text-sm hover:bg-gray-50 shadow-sm">Cancelar</button>
      <button type="button" id="modal-breed-save" class="px-6 py-3 rounded-md text-white btn-green">Agregar raza</button>
    </div>
  </div>
</div>

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

    const speciesSelect = document.getElementById('pet-species');
    const breedSelect = document.getElementById('pet-breed');

    async function loadBreedsForSpecies(species, preselectNameOrId) {
      breedSelect.innerHTML = '<option value="">Cargando...</option>';
      if (!species) {
        breedSelect.innerHTML = '<option value="">Seleccione especie primero</option>';
        return;
      }

      try {
        const res = await fetch(breedEndpoint + '/' + encodeURIComponent(species), { headers: { 'Accept': 'application/json' } });
        if (!res.ok) {
          breedSelect.innerHTML = '<option value="">No se encontraron razas</option>';
          return;
        }
        const payload = await res.json();
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

        if (preselectNameOrId) {
          const byId = breedSelect.querySelector('option[value="' + preselectNameOrId + '"]');
          if (byId) {
            breedSelect.value = preselectNameOrId;
          } else {
            // try by name
            for (const opt of breedSelect.options) {
              if (opt.textContent.trim().toLowerCase() === String(preselectNameOrId).trim().toLowerCase()) {
                breedSelect.value = opt.value;
                break;
              }
            }
          }
        }
      } catch (err) {
        console.error('Error fetching breeds', err);
        breedSelect.innerHTML = '<option value="">Error al cargar razas</option>';
      }
    }

    speciesSelect.addEventListener('change', function (e) {
      loadBreedsForSpecies(e.target.value);
    });


    const clientInput = document.getElementById('client-search');
    const clientResults = document.getElementById('client-results');
    const clientIdInput = document.getElementById('client-id');
    let clientTimer = null;

    function scheduleClientSearch(q) {
      if (clientTimer) clearTimeout(clientTimer);
      clientTimer = setTimeout(() => fetchClients(q), 250);
    }

    clientInput.addEventListener('input', function (e) {
      const q = e.target.value.trim();
      clientIdInput.value = '';
      clientResults.innerHTML = '';
      if (!q) {
        clientResults.style.display = 'none';
        return;
      }
      scheduleClientSearch(q);
    });

    clientInput.addEventListener('focus', function (e) {
      const q = e.target.value.trim();
      if (q) scheduleClientSearch(q);
    });

    async function fetchClients(q) {
      try {
        const res = await fetch(clientEndpoint + '/' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } });
        if (!res.ok) {
          return;
        }
        const payload = await res.json();
        const list = Array.isArray(payload) ? payload : (payload.data || []);
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

      // improved card-like result items
      list.forEach(c => {
        const wrapper = document.createElement('div');
        wrapper.className = 'px-3 py-2 hover:bg-gray-50 cursor-pointer flex items-center gap-3 transition-colors duration-150';

        // avatar / initials
        const avatar = document.createElement('div');
        avatar.className = 'w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-sm font-semibold text-gray-700';
        const initials = ((c.name ?? c.nombre ?? '').charAt(0) || '') + ((c.last_name_primary || '').charAt(0) || '');
        avatar.textContent = initials.toUpperCase();

        // main info
        const info = document.createElement('div');
        info.className = 'flex-1 min-w-0';
        const title = document.createElement('div');
        title.className = 'text-sm font-medium text-gray-900 truncate';
        title.textContent = (c.name ?? c.nombre ?? '') + (c.last_name_primary ? ' ' + c.last_name_primary : '') + (c.last_name_secondary ? ' ' + c.last_name_secondary : '');
        const meta = document.createElement('div');
        meta.className = 'text-xs text-gray-500 truncate';
        const parts = [];
        if (c.email) parts.push(c.email);
        if (c.phone) parts.push(c.phone);
        meta.textContent = parts.join(' — ');

        info.appendChild(title);
        info.appendChild(meta);

        // action icon (chevron)
        const chevron = document.createElement('div');
        chevron.className = 'text-gray-400';
        chevron.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>';

        wrapper.appendChild(avatar);
        wrapper.appendChild(info);
        wrapper.appendChild(chevron);

        wrapper.addEventListener('click', function () {
          // on selection fill input with readable name and set id
          clientInput.value = title.textContent;
          clientIdInput.value = c.id ?? c.client_id ?? '';
          clientResults.style.display = 'none';
        });

        clientResults.appendChild(wrapper);
      });
      clientResults.style.display = 'block';
    }

    const photoInput = document.getElementById('photo-input');
    const photoPreview = document.getElementById('photo-preview');

    const params = new URLSearchParams(window.location.search);
    const editId = params.get('edit');
    const isEdit = !!editId;
    const title = document.querySelector('h1');
    const submitBtn = document.getElementById('pet-create-submit');
    if (isEdit) {
      title.textContent = 'Editar Mascota';
      submitBtn.textContent = 'Guardar cambios';
      if (photoInput) photoInput.removeAttribute('required');
      (async function prefill(){
        try {
          const res = await fetch(apiUrl, { headers: {'Accept':'application/json'} });
          if (res.status !== 200) return;
          const list = await res.json();
          const pet = Array.isArray(list) ? list.find(x => String(x.id) === String(editId)) : null;
          if (!pet) return;
          form.querySelector('[name="name"]').value = pet.name || '';
          form.querySelector('[name="birth_date"]').value = pet.birth_date ? pet.birth_date.split('T')[0] : '';
          form.querySelector('[name="color"]').value = pet.color || '';
          form.querySelector('[name="species"]').value = pet.species || '';
          form.querySelector('[name="sex"]').value = pet.sex || '';
          setTimeout(() => {
            if (pet.breed_id) {
              const opt = document.createElement('option');
              opt.value = pet.breed_id;
              opt.textContent = pet.breed?.name ?? 'Raza';
              breedSelect.innerHTML = '';
              breedSelect.appendChild(opt);
              breedSelect.value = pet.breed_id;
            }
          }, 200);
          if (pet.client) {
            document.getElementById('client-search').value = (pet.client.name ?? '') + (pet.client.last_name_primary ? ' ' + pet.client.last_name_primary : '');
            document.getElementById('client-id').value = pet.client.id ?? pet.client_id ?? '';
          }
          if (pet.photo_url) {
            const img = document.createElement('img');
            img.src = pet.photo_url;
            img.alt = pet.name || 'Foto';
            img.className = 'w-32 h-32 object-cover rounded';
            photoPreview.appendChild(img);
          }
        } catch (err) { console.error('Prefill failed', err); }
      })();
    }

    if (photoInput) {
      photoInput.addEventListener('change', function () {
        photoPreview.innerHTML = '';
        const file = this.files && this.files[0];
        if (!file) return;
        const img = document.createElement('img');
        img.className = 'w-32 h-32 object-cover rounded';
        img.alt = 'Preview';
        const reader = new FileReader();
        reader.onload = function (ev) { img.src = ev.target.result; };
        reader.readAsDataURL(file);
        photoPreview.appendChild(img);
      });
    }

    form.addEventListener('submit', async function (e) {
      e.preventDefault();
      clearErrors();
      const submit = document.getElementById('pet-create-submit');
      submit.disabled = true;
      submit.classList.add('opacity-70');

      // Validación cliente en español
      function addFieldError(name, message) {
        const field = form.querySelector('[name="' + name + '"]');
        if (!field) return;
        field.setAttribute('aria-invalid', 'true');
        field.parentNode.querySelectorAll('.text-sm.text-red-600').forEach(el => el.remove());
        const p = document.createElement('p');
        p.className = 'text-sm text-red-600 mt-1';
        p.textContent = message;
        field.parentNode.appendChild(p);
      }

      const nameVal = (form.querySelector('[name="name"]').value || '').trim();
      const birthVal = (form.querySelector('[name="birth_date"]').value || '').trim();
      const colorVal = (form.querySelector('[name="color"]').value || '').trim();
      const speciesVal = (form.querySelector('[name="species"]').value || '').trim();
      const sexVal = (form.querySelector('[name="sex"]').value || '').trim();
      const breedVal = (form.querySelector('[name="breed_id"]').value || '').trim();
      const clientIdVal = (form.querySelector('[name="client_id"]').value || '').trim();

      if (!nameVal) { addFieldError('name', 'Debes ingresar el nombre de la mascota.'); submit.disabled = false; submit.classList.remove('opacity-70'); return; }
      if (!birthVal) { addFieldError('birth_date', 'Debes ingresar la fecha de nacimiento.'); submit.disabled = false; submit.classList.remove('opacity-70'); return; }
      if (!colorVal) { addFieldError('color', 'Debes ingresar el color.'); submit.disabled = false; submit.classList.remove('opacity-70'); return; }
      if (!speciesVal) { addFieldError('species', 'Debes seleccionar una especie.'); submit.disabled = false; submit.classList.remove('opacity-70'); return; }
      if (!sexVal) { addFieldError('sex', 'Debes seleccionar el sexo.'); submit.disabled = false; submit.classList.remove('opacity-70'); return; }
      if (!breedVal) { addFieldError('breed_id', 'Debes seleccionar una raza.'); submit.disabled = false; submit.classList.remove('opacity-70'); return; }
      if (!clientIdVal) { addFieldError('client_search', 'Debes seleccionar un cliente.'); submit.disabled = false; submit.classList.remove('opacity-70'); return; }

      const formData = new FormData(form);
      formData.delete('client_search');

      const tokenInput = form.querySelector('input[name="_token"]');
      const headers = { 'Accept': 'application/json' };
      if (tokenInput) headers['X-CSRF-TOKEN'] = tokenInput.value;

        try {
        let url = apiUrl;
        if (isEdit) {
          formData.append('_method', 'PATCH');
          url = apiUrl + '/' + editId;
        }

        const fetchOptions = {
          method: 'POST',
          headers: headers,
          body: formData,
          credentials: 'same-origin'
        };

  const res = await fetch(url, fetchOptions);

        if (res.status === 201 || res.status === 200) {
          if (window.showToast) showToast('Mascota guardada correctamente', { type: 'success' });
          window.location.href = redirectUrl;
          return;
        }

        if (res.status === 422) {
          const payload = await res.json();
          if (payload && payload.errors) showErrors(payload.errors);
          submit.disabled = false;
          submit.classList.remove('opacity-70');
          return;
        }

        console.error('Unexpected response', res.status);
        if (window.showToast) showToast('Ocurrió un error al intentar guardar la mascota. Intenta de nuevo.', { type: 'error' });
      } catch (err) {
        console.error('Request failed', err);
        if (window.showToast) showToast('No se pudo conectar con el servidor.', { type: 'error' });
      } finally {
        submit.disabled = false;
        submit.classList.remove('opacity-70');
      }
    });

  })();
</script>
