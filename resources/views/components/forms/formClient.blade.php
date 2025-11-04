@php
  $apiUrl = url('/api/clients');
  $redirectUrl = url('/dashboard/clientes');
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
      <h1 class="text-3xl font-extrabold mb-1" style="font-family: var(--font-primary); color: var(--text-title);">Agregar Cliente</h1>
      <p class="text-sm text-gray-600">Completa el formulario para agregar un nuevo cliente</p>
    </div>
  </div>

  <div class="max-w-3xl bg-white p-6 rounded shadow mx-auto">
    <form id="client-create-form" class="space-y-4" action="#" method="post" novalidate>
      @csrf

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Nombre <span class="text-red-500">*</span></label>
          <input name="name" type="text" placeholder="Ej: Juan" class="form-control mt-1 block w-full" required>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Apellido Paterno <span class="text-red-500">*</span></label>
          <input name="last_name_primary" type="text" placeholder="Ej: Pérez" class="form-control mt-1 block w-full" required>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Apellido Materno <span class="text-red-500">*</span></label>
          <input name="last_name_secondary" type="text" placeholder="Ej: López" class="form-control mt-1 block w-full">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Teléfono <span class="text-red-500">*</span></label>
          <input name="phone" type="text" placeholder="5550000000" class="form-control mt-1 block w-full" required>
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700">Correo Electrónico <span class="text-red-500">*</span></label>
          <input name="email" type="email" placeholder="correo@ejemplo.com" class="form-control mt-1 block w-full" required>
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700">Dirección <span class="text-red-500">*</span></label>
          <input name="address" type="text" placeholder="Calle, número, colonia" class="form-control mt-1 block w-full">
        </div>
      </div>

      <div class="mt-6">
        <div class="flex justify-center">
          <button type="submit" id="client-create-submit" class="px-8 py-3 rounded-md text-white btn-green text-lg w-full md:w-1/3">Guardar Cliente</button>
        </div>
      </div>
    </form>
  </div>
</main>

<script>
  (function () {
    const form = document.getElementById('client-create-form');
    if (!form) return;

    const apiUrl = @json($apiUrl);
    const redirectUrl = @json($redirectUrl);

    const params = new URLSearchParams(window.location.search);
    const editId = params.get('edit');
    let isEdit = !!editId;

    const titleEl = document.querySelector('h1');
    const submitBtn = document.getElementById('client-create-submit');

    if (isEdit) {
      if (titleEl) titleEl.textContent = 'Editar Cliente';
      if (submitBtn) submitBtn.textContent = 'Guardar cambios';
      (async function prefill() {
        try {
          const res = await fetch(apiUrl, { credentials: 'same-origin' });
          if (!res.ok) throw new Error('HTTP ' + res.status);
          const clients = await res.json();
          const client = (clients || []).find(c => String(c.id) === String(editId));
          if (!client) {
            if (window.showToast) window.showToast('Cliente no encontrado', { type: 'error' });
            return;
          }
          form.querySelector('[name="name"]').value = client.name || '';
          form.querySelector('[name="last_name_primary"]').value = client.last_name_primary || '';
          form.querySelector('[name="last_name_secondary"]').value = client.last_name_secondary || '';
          form.querySelector('[name="phone"]').value = client.phone || '';
          form.querySelector('[name="email"]').value = client.email || '';
          form.querySelector('[name="address"]').value = client.address || '';
        } catch (err) {
          console.error('Prefill failed', err);
          if (window.showToast) window.showToast('No se pudo obtener datos del cliente para editar.', { type: 'error' });
        }
      })();
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
      const submit = submitBtn;
      if (submit) {
        submit.disabled = true;
        submit.classList.add('opacity-70');
      }

      const data = {};
      new FormData(form).forEach((v,k) => { data[k] = v; });

      const tokenInput = form.querySelector('input[name="_token"]');
      const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
      if (tokenInput) headers['X-CSRF-TOKEN'] = tokenInput.value;

      try {
        const method = isEdit ? 'PATCH' : 'POST';
        const url = isEdit ? apiUrl + '/' + encodeURIComponent(editId) : apiUrl;

        const res = await fetch(url, {
          method: method,
          headers: headers,
          body: JSON.stringify(data),
          credentials: 'same-origin'
        });

        if (res.status === 201 || res.status === 200) {
          if (window.showToast) window.showToast(isEdit ? 'Cliente actualizado' : 'Cliente creado', { type: 'success' });
          window.location.href = redirectUrl;
          return;
        }

        if (res.status === 422) {
          const payload = await res.json();
          if (payload && payload.errors) showErrors(payload.errors);
          else console.warn('Validation failed but no errors object', payload);
          if (submit) {
            submit.disabled = false;
            submit.classList.remove('opacity-70');
          }
          return;
        }

        console.error('Unexpected response', res.status);
        if (window.showToast) {
          window.showToast('Ocurrió un error al intentar guardar el cliente. Intenta de nuevo.', { type: 'error' });
        } else {
          alert('Ocurrió un error al intentar guardar el cliente. Intenta de nuevo.');
        }
      } catch (err) {
        console.error('Request failed', err);
        if (window.showToast) {
          window.showToast('No se pudo conectar con el servidor.', { type: 'error' });
        } else {
          alert('No se pudo conectar con el servidor.');
        }
      } finally {
        if (submit) {
          submit.disabled = false;
          submit.classList.remove('opacity-70');
        }
      }
    });
  })();
</script>
