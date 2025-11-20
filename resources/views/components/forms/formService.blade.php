@php
  $apiUrl = url('/api/services');
  $redirectUrl = url('/dashboard/servicios');
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
      <h1 class="text-3xl font-extrabold mb-1" style="font-family: var(--font-primary); color: var(--text-title);">Agregar Servicio</h1>
      <p class="text-sm text-gray-600">Rellena los datos del servicio</p>
    </div>
  </div>

    <div class="max-w-3xl bg-white p-6 rounded shadow mx-auto">
      <form id="service-create-form" class="space-y-4" action="#" method="post" enctype="multipart/form-data" novalidate>
      @csrf
      <div class="grid grid-cols-1 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Nombre <span class="text-red-500">*</span></label>
          <input name="name" type="text" placeholder="Ej: Corte de pelo" class="form-control mt-1 block w-full" required>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Descripción <span class="text-red-500">*</span></label>
          <textarea name="description" rows="4" class="form-control mt-1 block w-full" placeholder="Descripción del servicio" required></textarea>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Precio <span class="text-red-500">*</span></label>
          <input name="price" type="number" step="0.01" placeholder="0.00" class="form-control mt-1 block w-full" required>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Foto (archivo) <span class="text-red-500">*</span></label>
          <input name="photo" id="photo-input" type="file" accept="image/*" class="form-control mt-1 block w-full">
          <div id="photo-preview" class="mt-2"></div>
        </div>
      </div>

      <div class="mt-6">
        <div class="flex justify-center">
          <button type="submit" id="service-create-submit" class="px-8 py-3 rounded-md text-white btn-green text-lg w-full md:w-1/3">Guardar Servicio</button>
        </div>
      </div>
    </form>
  </div>
</main>

<script>
  (function () {
    const form = document.getElementById('service-create-form');
    if (!form) return;

    const apiUrl = @json($apiUrl);
    const redirectUrl = @json($redirectUrl);

    const params = new URLSearchParams(window.location.search);
    const editId = params.get('edit');
    const isEdit = !!editId;
    const photoInput = form.querySelector('#photo-input');
    const photoPreview = form.querySelector('#photo-preview');
    const title = document.querySelector('h1');
    const submitBtn = document.getElementById('service-create-submit');
    if (isEdit) {
      title.textContent = 'Editar Servicio';
      submitBtn.textContent = 'Guardar cambios';
      if (photoInput) photoInput.removeAttribute('required');
      (async function prefill(){
        try {
          const res = await fetch(apiUrl, { headers: {'Accept':'application/json'} });
          if (res.status !== 200) return;
          const list = await res.json();
          const svc = Array.isArray(list) ? list.find(x => String(x.id) === String(editId)) : null;
          if (!svc) return;
          form.querySelector('[name="name"]').value = svc.name || '';
          form.querySelector('[name="description"]').value = svc.description || '';
          form.querySelector('[name="price"]').value = svc.price || '';
          if (svc.photo_url) {
            const img = document.createElement('img');
            img.src = svc.photo_url;
            img.alt = svc.name || 'Foto';
            img.className = 'w-32 h-32 object-cover rounded';
            photoPreview.appendChild(img);
          }
        } catch (err) {
          console.error('Prefill failed', err);
        }
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

      form.addEventListener('submit', async function (e) {
      e.preventDefault();
      clearErrors();
      const submit = document.getElementById('service-create-submit');
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
      const descVal = (form.querySelector('[name="description"]').value || '').trim();
      const priceVal = (form.querySelector('[name="price"]').value || '').trim();
      if (!nameVal) { addFieldError('name', 'Debes ingresar el nombre del servicio.'); submit.disabled = false; submit.classList.remove('opacity-70'); return; }
      if (!descVal) { addFieldError('description', 'Debes ingresar la descripción del servicio.'); submit.disabled = false; submit.classList.remove('opacity-70'); return; }
      if (!priceVal) { addFieldError('price', 'Debes ingresar el precio del servicio.'); submit.disabled = false; submit.classList.remove('opacity-70'); return; }
      if (isNaN(Number(priceVal)) || Number(priceVal) < 0) { addFieldError('price', 'El precio debe ser un número válido mayor o igual a 0.'); submit.disabled = false; submit.classList.remove('opacity-70'); return; }

      const formData = new FormData(form);
          try {
            for (const pair of formData.entries()) {
              console.debug('formData', pair[0], pair[1]);
            }
          } catch (e) { console.debug('formData inspect error', e); }
      const tokenInput = form.querySelector('input[name="_token"]');
      const headers = { 'Accept': 'application/json' };
      if (tokenInput) headers['X-CSRF-TOKEN'] = tokenInput.value;

      try {
        let url = apiUrl;
        if (isEdit) {
          formData.append('_method', 'PATCH');
          url = apiUrl + '/' + editId;
        }
        const res = await fetch(url, {
          method: 'POST',
          headers: headers,
          body: formData,
          credentials: 'same-origin'
        });

        if (res.status === 201 || res.status === 200) {
          if (window.showToast) showToast('Servicio creado correctamente', { type: 'success' });
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
        if (window.showToast) showToast('Ocurrió un error al intentar crear el servicio.', { type: 'error' });
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
