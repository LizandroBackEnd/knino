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
    <form id="service-create-form" class="space-y-4" action="#" method="post" novalidate>
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
          <label class="block text-sm font-medium text-gray-700">Foto (URL) <span class="text-red-500">*</span></label>
          <input name="photo_url" type="url" placeholder="https://ejemplo.com/imagen.jpg" class="form-control mt-1 block w-full" required>
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

      const data = {};
      new FormData(form).forEach((v,k) => { data[k] = v; });

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
