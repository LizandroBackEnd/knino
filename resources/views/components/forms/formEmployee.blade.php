@php
  $apiUrl = url('/api/employees');
  $redirectUrl = url('/dashboard/empleados');
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
      <h1 class="text-3xl font-extrabold mb-1" style="font-family: var(--font-primary); color: var(--text-title);">Agregar Empleado</h1>
      <p class="text-sm text-gray-600">Completa los campos para crear un nuevo empleado</p>
    </div>
  </div>

  <div class="max-w-3xl bg-white p-6 rounded shadow mx-auto">
    <form id="employee-create-form" class="space-y-4" action="#" method="post" novalidate>
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
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700">Correo Electrónico <span class="text-red-500">*</span></label>
          <input name="email" type="email" placeholder="correo@ejemplo.com" class="form-control mt-1 block w-full" required>
        </div>

      </div>

        <div class="mt-4">
          <label class="block text-sm font-medium text-gray-700">Horarios de trabajo <span class="text-red-500">*</span></label>
          <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end mt-2">
            <div>
              <label class="block text-sm text-gray-700">Día inicio <span class="text-red-500">*</span></label>
              <select id="sched-day-start" class="form-control mt-1 block w-full">
                <option value="0">Domingo</option>
                <option value="1">Lunes</option>
                <option value="2">Martes</option>
                <option value="3">Miércoles</option>
                <option value="4">Jueves</option>
                <option value="5">Viernes</option>
                <option value="6">Sábado</option>
              </select>
            </div>
            <div>
              <label class="block text-sm text-gray-700">Día fin <span class="text-red-500">*</span></label>
              <select id="sched-day-end" class="form-control mt-1 block w-full">
                <option value="0">Domingo</option>
                <option value="1">Lunes</option>
                <option value="2">Martes</option>
                <option value="3">Miércoles</option>
                <option value="4">Jueves</option>
                <option value="5">Viernes</option>
                <option value="6">Sábado</option>
              </select>
            </div>
            <div>
              <label class="block text-sm text-gray-700">Inicio <span class="text-red-500">*</span></label>
              <select id="sched-start" name="start_time" aria-required="true" required class="form-control mt-1 block w-full">
                <option value="08:00">08:00</option>
                <option value="09:00">09:00</option>
                <option value="10:00">10:00</option>
              </select>
            </div>
            <div>
              <label class="block text-sm text-gray-700">Fin <span class="text-red-500">*</span></label>
              <select id="sched-end" name="end_time" aria-required="true" required class="form-control mt-1 block w-full">
                <option value="11:00">11:00</option>
                <option value="12:00">12:00</option>
                <option value="13:00">1:00</option>
                <option value="14:00">2:00</option>
                <option value="15:00">3:00</option>
                <option value="16:00">4:00</option>
              </select>
            </div>
            
          </div>

          <div class="mt-3">
            <ul id="schedule-list" class="space-y-2"></ul>
          </div>
        </div>

      <div class="mt-6">
        <div class="flex justify-center">
          <button type="submit" id="employee-create-submit" class="px-8 py-3 rounded-md text-white btn-green text-lg w-full md:w-1/3">Crear Empleado</button>
        </div>
      </div>
    </form>
  </div>
</main>


<script>
  (function () {
    const form = document.getElementById('employee-create-form');
    if (!form) return;

    const apiUrl = @json($apiUrl);
    const redirectUrl = @json($redirectUrl);

    const params = new URLSearchParams(window.location.search);
    const editId = params.get('edit');
    let isEdit = !!editId;

    const titleEl = document.querySelector('h1');
    const submitBtn = document.getElementById('employee-create-submit');

    // schedules local store
    let schedules = [];
  const schedDayStart = document.getElementById('sched-day-start');
  const schedDayEnd = document.getElementById('sched-day-end');
  const schedStart = document.getElementById('sched-start');
  const schedEnd = document.getElementById('sched-end');
  const scheduleList = document.getElementById('schedule-list');
  // allowed ranges
  const START_MIN = '08:00';
  const START_MAX = '10:00';
  const END_MIN = '11:00';
  const END_MAX = '16:00';

    function renderSchedules() {
      scheduleList.innerHTML = '';
      schedules.forEach((s, idx) => {
        const li = document.createElement('li');
        li.className = 'flex items-center justify-between p-2 border rounded';
        const label = document.createElement('div');
        const days = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
        const startDay = (typeof s.day_start !== 'undefined') ? s.day_start : (typeof s.day_of_week_start !== 'undefined' ? s.day_of_week_start : s.day_of_week);
        const endDay = (typeof s.day_end !== 'undefined') ? s.day_end : (typeof s.day_of_week_end !== 'undefined' ? s.day_of_week_end : s.day_of_week);
        const dayLabel = startDay === endDay ? days[startDay] : `${days[startDay]} - ${days[endDay]}`;
        label.innerHTML = `<strong>${dayLabel}</strong> ${s.start_time} - ${s.end_time} ${s.active ? '<span class="text-sm text-green-600 ml-2">(activo)</span>' : '<span class="text-sm text-gray-500 ml-2">(inactivo)</span>'}`;
        const btns = document.createElement('div');
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'px-2 py-1 text-sm text-red-600';
        removeBtn.textContent = 'Eliminar';
        removeBtn.addEventListener('click', () => {
          schedules.splice(idx, 1);
          renderSchedules();
        });
        btns.appendChild(removeBtn);
        li.appendChild(label);
        li.appendChild(btns);
        scheduleList.appendChild(li);
      });
    }

    // (no preset buttons) 

    if (isEdit) {
      if (titleEl) titleEl.textContent = 'Editar Empleado';
      if (submitBtn) submitBtn.textContent = 'Guardar cambios';
      (async function prefill() {
        try {
          const res = await fetch(apiUrl, { credentials: 'same-origin' });
          if (!res.ok) throw new Error('HTTP ' + res.status);
          const items = await res.json();
          const emp = (items || []).find(e => String(e.id) === String(editId));
          if (!emp) { if (window.showToast) window.showToast('Empleado no encontrado', { type: 'error' }); return; }
          form.querySelector('[name="name"]').value = emp.name || '';
          form.querySelector('[name="last_name_primary"]').value = emp.last_name_primary || '';
          form.querySelector('[name="last_name_secondary"]').value = emp.last_name_secondary || '';
          form.querySelector('[name="phone"]').value = emp.phone || '';
          form.querySelector('[name="email"]').value = emp.email || '';

          // fetch schedules for this employee
          try {
            const sres = await fetch(`${apiUrl}/${encodeURIComponent(editId)}/schedules`, { credentials: 'same-origin' });
              if (sres.ok) {
              const sdata = await sres.json();
              // normalize schedules: support legacy (day_of_week) and new (day_of_week_start/day_of_week_end)
              schedules = (sdata || []).map(s => ({
                day_start: (typeof s.day_of_week_start !== 'undefined') ? s.day_of_week_start : (typeof s.day_start !== 'undefined' ? s.day_start : s.day_of_week),
                day_end: (typeof s.day_of_week_end !== 'undefined') ? s.day_of_week_end : (typeof s.day_end !== 'undefined' ? s.day_end : s.day_of_week),
                start_time: s.start_time,
                end_time: s.end_time,
                active: !!s.active,
                id: s.id
              }));
              // if schedules preloaded, set select values to first schedule for editing clarity
              if (schedules.length > 0) {
                const first = schedules[0];
                if (schedDayStart) schedDayStart.value = String(first.day_start);
                if (schedDayEnd) schedDayEnd.value = String(first.day_end);
                if (schedStart) schedStart.value = first.start_time;
                if (schedEnd) schedEnd.value = first.end_time;
              }
              renderSchedules();
            }
          } catch (serr) {
            console.warn('Could not fetch schedules', serr);
          }

        } catch (err) {
          console.error('Prefill failed', err);
          if (window.showToast) window.showToast('No se pudo obtener datos del empleado para editar.', { type: 'error' });
        }
      })();
    }

    // If user didn't press an "Agregar" button (we removed it), allow submitting the current inputs as a single schedule
    function pushInlineScheduleIfPresent() {
      // if there are already schedules (e.g. in edit mode) do nothing
      if (schedules.length > 0) return;
      const dayStart = parseInt(schedDayStart.value, 10);
      const dayEnd = parseInt(schedDayEnd.value, 10);
      const start = schedStart.value;
      const end = schedEnd.value;
      const active = true; // schedules are active by default (checkbox removed)
      if (!start || !end) {
        const msg = 'Debes ingresar la hora de inicio y la hora de fin para el horario.';
        if (window.showToast) window.showToast(msg, { type: 'error' }); else alert(msg);
        return;
      }
      // validate start/end ranges
      if (start < START_MIN || start > START_MAX) {
        const msg = `La hora de inicio debe estar entre ${START_MIN} y ${START_MAX}.`;
        if (window.showToast) window.showToast(msg, { type: 'error' }); else alert(msg);
        return;
      }
      if (end < END_MIN || end > END_MAX) {
        const msg = `La hora de salida debe estar entre ${END_MIN} y ${END_MAX}.`;
        if (window.showToast) window.showToast(msg, { type: 'error' }); else alert(msg);
        return;
      }
      if (start >= end) {
        const msg = 'La hora de inicio debe ser anterior a la hora de fin.';
        if (window.showToast) window.showToast(msg, { type: 'error' }); else alert(msg);
        return;
      }
      // validate day range (start must be <= end)
      if (dayStart > dayEnd) {
        const msg = 'El día de inicio debe ser anterior o igual al día de fin.';
        if (window.showToast) window.showToast(msg, { type: 'error' }); else alert(msg);
        return;
      }
      schedules.push({ day_start: dayStart, day_end: dayEnd, start_time: start, end_time: end, active });
      renderSchedules();
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
      if (!data.phone || String(data.phone).trim() === '') { addFieldError('phone', 'Debes ingresar un teléfono.'); if (submit) { submit.disabled = false; submit.classList.remove('opacity-70'); } return; }
      if (!data.email || String(data.email).trim() === '') { addFieldError('email', 'Debes ingresar un correo electrónico.'); if (submit) { submit.disabled = false; submit.classList.remove('opacity-70'); } return; }

      // if schedules array is empty, attempt to use inline inputs (we removed the separate "Agregar" button)
      pushInlineScheduleIfPresent();
      if (!schedules || !Array.isArray(schedules) || schedules.length === 0) {
        if (window.showToast) window.showToast('Debes agregar al menos un horario de trabajo para el empleado.', { type: 'error' });
        if (submit) { submit.disabled = false; submit.classList.remove('opacity-70'); }
        return;
      }

      const emailVal = String(data.email).trim();
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)) { addFieldError('email', 'Por favor ingresa un correo electrónico válido.'); if (submit) { submit.disabled = false; submit.classList.remove('opacity-70'); } return; }

      const phoneVal = String(data.phone).trim();
      if (!/^[0-9]+$/.test(phoneVal)) { addFieldError('phone', 'El teléfono debe contener solo números.'); if (submit) { submit.disabled = false; submit.classList.remove('opacity-70'); } return; }
      data.phone = String(phoneVal);

      const tokenInput = form.querySelector('input[name="_token"]');
      const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
      if (tokenInput) headers['X-CSRF-TOKEN'] = tokenInput.value;

      try {
  const method = isEdit ? 'PATCH' : 'POST';
  const url = isEdit ? apiUrl + '/' + encodeURIComponent(editId) : apiUrl;

      // include schedules in payload (mandatory)
  // If we're editing and the user changed the inline selects, make sure those values are applied
  // to the first schedule in memory (the form UI edits the selects but schedules[] isn't automatically updated).
  try {
    if (schedules.length > 0) {
      const curDayStart = parseInt(schedDayStart.value, 10);
      const curDayEnd = parseInt(schedDayEnd.value, 10);
      const curStart = schedStart.value;
      const curEnd = schedEnd.value;
      // update first schedule with current selects (keeps other schedules untouched)
      schedules[0].day_start = curDayStart;
      schedules[0].day_end = curDayEnd;
      schedules[0].start_time = curStart;
      schedules[0].end_time = curEnd;
    }
  } catch (e) {
    // ignore if selects not present
  }

  // map local schedule objects to API field names expected by controller
  data.schedules = schedules.map(s => ({
    day_of_week_start: typeof s.day_start !== 'undefined' ? s.day_start : (typeof s.day_of_week_start !== 'undefined' ? s.day_of_week_start : (typeof s.day_of_week !== 'undefined' ? s.day_of_week : null)),
    day_of_week_end: typeof s.day_end !== 'undefined' ? s.day_end : (typeof s.day_of_week_end !== 'undefined' ? s.day_of_week_end : (typeof s.day_of_week !== 'undefined' ? s.day_of_week : null)),
    start_time: s.start_time,
    end_time: s.end_time,
    active: s.active
  }));

  const res = await fetch(url, { method: method, headers: headers, body: JSON.stringify(data), credentials: 'same-origin' });

        if (res.status === 201 || res.status === 200) {
          if (window.showToast) window.showToast(isEdit ? 'Empleado actualizado' : 'Empleado creado correctamente', { type: 'success' });
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
        if (window.showToast) window.showToast('Ocurrió un error al intentar guardar el empleado.', { type: 'error' });
      } catch (err) {
        console.error('Request failed', err);
        if (window.showToast) window.showToast('No se pudo conectar con el servidor.', { type: 'error' });
      } finally {
        if (submit) { submit.disabled = false; submit.classList.remove('opacity-70'); }
      }
    });
  })();
</script>
