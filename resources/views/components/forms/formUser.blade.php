@php
  $apiUrl = url('/api/users');
  $redirectUrl = url('/dashboard/users');
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
      <h1 class="text-3xl font-extrabold mb-1" style="font-family: var(--font-primary); color: var(--text-title);">Agregar Usuario</h1>
      <p class="text-sm text-gray-600">Completa los campos para crear un nuevo usuario</p>
    </div>
  </div>

  <div class="max-w-3xl bg-white p-6 rounded shadow mx-auto">
  <form id="user-create-form" class="space-y-4" action="#" method="post" novalidate>
      @csrf

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Nombre <span class="text-red-500">*</span></label>
          <input name="name" type="text" placeholder="Ej: Ana" class="form-control mt-1 block w-full" required>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Apellido Paterno <span class="text-red-500">*</span></label>
          <input name="last_name_primary" type="text" placeholder="Ej: López" class="form-control mt-1 block w-full" required>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Apellido Materno <span class="text-red-500">*</span></label>
          <input name="last_name_secondary" type="text" placeholder="Ej: García" class="form-control mt-1 block w-full" required>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Teléfono <span class="text-red-500">*</span></label>
          <input name="phone" type="text" placeholder="5550000000" class="form-control mt-1 block w-full" required>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Rol <span class="text-red-500">*</span></label>
          <select name="role" class="form-control mt-1 block w-full" required>
            <option value="">Selecciona un rol</option>
            <option value="admin">Admin</option>
            <option value="receptionist">Recepción</option>
            <option value="veterinarian">Veterinario</option>
          </select>
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700">Correo Electrónico <span class="text-red-500">*</span></label>
          <input name="email" type="email" placeholder="correo@ejemplo.com" class="form-control mt-1 block w-full" required>
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700">Contraseña <span class="text-red-500">*</span></label>
          <input name="password" type="password" placeholder="********" class="form-control mt-1 block w-full">
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700">Confirmar contraseña <span class="text-red-500">*</span></label>
          <input name="password_confirmation" type="password" placeholder="********" class="form-control mt-1 block w-full">
        </div>
      </div>

      <div class="mt-6">
        <div class="flex justify-center">
          <button type="submit" id="user-create-submit" class="px-8 py-3 rounded-md text-white btn-green text-lg w-full md:w-1/3">Crear Usuario</button>
        </div>
      </div>
    </form>
  </div>
</main>

<script>
  (function () {
  const form = document.getElementById('user-create-form');
    if (!form) return;

    const apiUrl = @json($apiUrl);
    const redirectUrl = @json($redirectUrl);

    // detect edit mode
    const params = new URLSearchParams(window.location.search);
    const editId = params.get('edit');
    let isEdit = !!editId;

    const titleEl = document.querySelector('h1');
    const submitBtn = document.getElementById('user-create-submit');

    if (isEdit) {
      if (titleEl) titleEl.textContent = 'Editar Usuario';
      if (submitBtn) submitBtn.textContent = 'Guardar cambios';
      (async function prefill() {
        try {
          const res = await fetch(apiUrl, { credentials: 'same-origin' });
          if (!res.ok) throw new Error('HTTP ' + res.status);
          const items = await res.json();
          const emp = (items || []).find(e => String(e.id) === String(editId));
          if (!emp) { if (window.showToast) window.showToast('Usuario no encontrado', { type: 'error' }); return; }
          form.querySelector('[name="name"]').value = emp.name || '';
          form.querySelector('[name="last_name_primary"]').value = emp.last_name_primary || '';
          form.querySelector('[name="last_name_secondary"]').value = emp.last_name_secondary || '';
          form.querySelector('[name="phone"]').value = emp.phone || '';
          form.querySelector('[name="role"]').value = emp.role || '';
          form.querySelector('[name="email"]').value = emp.email || '';
        } catch (err) {
          console.error('Prefill failed', err);
          if (window.showToast) window.showToast('No se pudo obtener datos del usuario para editar.', { type: 'error' });
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
      if (submit) { submit.disabled = true; submit.classList.add('opacity-70'); }


      const data = {};
      new FormData(form).forEach((v,k) => { data[k] = v; });

      // Validación cliente en español (campos requeridos y formato)
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

      if (!data.name || String(data.name).trim() === '') { addFieldError('name', 'Debes ingresar el nombre.'); if (submit) { submit.disabled = false; submit.classList.remove('opacity-70'); } return; }
      if (!data.last_name_primary || String(data.last_name_primary).trim() === '') { addFieldError('last_name_primary', 'Debes ingresar el apellido paterno.'); if (submit) { submit.disabled = false; submit.classList.remove('opacity-70'); } return; }
      if (!data.email || String(data.email).trim() === '') { addFieldError('email', 'Debes ingresar un correo electrónico.'); if (submit) { submit.disabled = false; submit.classList.remove('opacity-70'); } return; }
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(data.email).trim())) { addFieldError('email', 'Por favor ingresa un correo electrónico válido.'); if (submit) { submit.disabled = false; submit.classList.remove('opacity-70'); } return; }
      if (!data.role || String(data.role).trim() === '') { addFieldError('role', 'Debes seleccionar un rol.'); if (submit) { submit.disabled = false; submit.classList.remove('opacity-70'); } return; }

      // Contraseña obligatoria al crear
      if (!isEdit) {
        if (!data.password || String(data.password).trim() === '') { addFieldError('password', 'Debes ingresar una contraseña.'); if (submit) { submit.disabled = false; submit.classList.remove('opacity-70'); } return; }
        if (data.password !== data.password_confirmation) { addFieldError('password_confirmation', 'Las contraseñas no coinciden.'); if (submit) { submit.disabled = false; submit.classList.remove('opacity-70'); } return; }
      }

      // Teléfono solo números
      const phoneVal = (data.phone || '').toString().trim();
      if (!/^[0-9]+$/.test(phoneVal)) { addFieldError('phone', 'El teléfono debe contener solo números.'); if (submit) { submit.disabled = false; submit.classList.remove('opacity-70'); } return; }
      data.phone = String(phoneVal);

      if (isEdit && (!data.password || data.password === '')) {
        delete data.password;
        delete data.password_confirmation;
      }

      const tokenInput = form.querySelector('input[name="_token"]');
      const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
      if (tokenInput) headers['X-CSRF-TOKEN'] = tokenInput.value;

      try {
        const method = isEdit ? 'PATCH' : 'POST';
        const url = isEdit ? apiUrl + '/' + encodeURIComponent(editId) : apiUrl;

        const res = await fetch(url, { method: method, headers: headers, body: JSON.stringify(data), credentials: 'same-origin' });

        if (res.status === 201 || res.status === 200) {
          if (window.showToast) window.showToast(isEdit ? 'Usuario actualizado' : 'Usuario creado correctamente', { type: 'success' });
          window.location.href = redirectUrl;
          return;
        }

        if (res.status === 422) {
          const payload = await res.json();
          if (payload && payload.errors) showErrors(payload.errors);
          if (submit) { submit.disabled = false; submit.classList.remove('opacity-70'); }
          return;
        }

        console.error('Unexpected response', res.status);
        if (window.showToast) window.showToast('Ocurrió un error al intentar guardar el usuario.', { type: 'error' });
      } catch (err) {
        console.error('Request failed', err);
        if (window.showToast) window.showToast('No se pudo conectar con el servidor.', { type: 'error' });
      } finally {
        if (submit) { submit.disabled = false; submit.classList.remove('opacity-70'); }
      }
    });
  })();
</script>
