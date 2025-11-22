@php
  use App\Models\enums\SizeEnum;
  $apiPets = url('/api/pets');
  $apiServices = url('/api/services');
  $apiVets = url('/api/appointments/veterinarians/available');
  $apiAppointments = url('/api/appointments');
  $sizes = SizeEnum::values();
  $redirectUrl = url('/dashboard/citas');

  // compute a safe string for the size display (avoid passing enum objects to ucfirst())
  $rawSize = data_get($appointment, 'size') ?? data_get($appointment, 'pet.size') ?? '';
  if (is_object($rawSize)) {
    // If enum/backed-enum, try ->value, otherwise cast to string
    if (property_exists($rawSize, 'value')) {
      $appt_size = $rawSize->value;
    } elseif (method_exists($rawSize, 'value')) {
      $appt_size = $rawSize->value();
    } else {
      $appt_size = (string) $rawSize;
    }
  } else {
    $appt_size = $rawSize;
  }

  // determine server-side reschedule mode: if an appointment was passed or reschedule query param exists
  $isReschedule = isset($appointment) || (request()->query('reschedule') !== null);
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
      <h1 class="text-3xl font-extrabold mb-1" style="font-family: var(--font-primary); color: var(--text-title);">Programar cita</h1>
      <p class="text-sm text-gray-600">Completa el formulario para agendar una nueva cita</p>
    </div>
  </div>

  <div class="max-w-3xl bg-white p-6 rounded shadow mx-auto">
    <form id="appointment-form" class="space-y-4" action="#" method="post" novalidate>
      @csrf

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Mascota <span class="text-red-500">*</span></label>
          <input id="pet-search" type="search" placeholder="Buscar mascota por nombre" class="form-control mt-1 block w-full" value="{{ data_get($appointment, 'pet.name') ?? data_get($appointment, 'pet_name') }}" @if($isReschedule) disabled @endif>
          <input id="pet-id" name="pet_id" type="hidden" value="{{ data_get($appointment, 'pet.id') ?? data_get($appointment, 'pet_id') }}">
          <div id="pet-results" class="mt-2 bg-white border rounded shadow" style="display:none; max-height:200px; overflow:auto;"></div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Cliente</label>
          <input id="client-display" name="client_display" type="text" readonly class="form-control mt-1 block w-full bg-gray-50" placeholder="Se completará al seleccionar mascota" value="{{ data_get($appointment, 'pet.client.name') ? (data_get($appointment, 'pet.client.name') . ' ' . data_get($appointment, 'pet.client.last_name_primary')) : (data_get($appointment, 'client.name') ? (data_get($appointment, 'client.name') . ' ' . data_get($appointment, 'client.last_name_primary')) : '') }}" @if($isReschedule) disabled @endif>
          <input id="client-id" name="client_id" type="hidden" value="{{ data_get($appointment, 'pet.client.id') ?? data_get($appointment, 'client_id') }}">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Servicio <span class="text-red-500">*</span></label>
          <input id="service-search" type="search" placeholder="Buscar servicio" class="form-control mt-1 block w-full" value="{{ data_get($appointment, 'service.name') ?? data_get($appointment, 'service_name') }}" @if($isReschedule) disabled @endif>
          <input id="service-id" name="service_id" type="hidden" value="{{ data_get($appointment, 'service.id') ?? data_get($appointment, 'service_id') }}">
          <div id="service-results" class="mt-2 bg-white border rounded shadow" style="display:none; max-height:200px; overflow:auto;"></div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Tamaño</label>
          {{-- Visible readonly display (auto-filled from mascota) and hidden input named `size` for form submit --}}
          <input id="appt-size-display" type="text" readonly class="form-control mt-1 block w-full bg-gray-50" placeholder="Se completará al seleccionar mascota" value="{{ $appt_size ? ucfirst($appt_size) : '' }}" @if($isReschedule) disabled @endif>
          <input id="appt-size" name="size" type="hidden" value="{{ data_get($appointment,'size') ?? data_get($appointment,'pet.size') ?? '' }}">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Precio</label>
          <input id="appt-price" name="price" type="text" readonly class="form-control mt-1 block w-full bg-gray-50" placeholder="Se calculará según tamaño" value="@if(isset($appointment) && ($appointment->price !== null)) ${{ number_format($appointment->price,2) }} @endif" @if($isReschedule) disabled @endif>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Peso (kg)</label>
          <input id="appt-weight" name="weight" type="number" step="0.01" min="0" class="form-control mt-1 block w-full" value="{{ data_get($appointment, 'pet.weight') ?? data_get($appointment, 'weight') }}" @if($isReschedule) disabled @endif>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Fecha <span class="text-red-500">*</span></label>
          <input id="scheduled-date" name="scheduled_date" type="date" class="form-control mt-1 block w-full" value="{{ isset($appointment) && $appointment->scheduled_at ? 
            (strpos($appointment->scheduled_at, 'T') !== false ? (new \Carbon\Carbon($appointment->scheduled_at))->toDateString() : explode(' ', $appointment->scheduled_at)[0]) : '' }}">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Hora <span class="text-red-500">*</span></label>
          @php
            // generate 30-minute slots from 08:00 to 15:30
            $timeSlots = [];
            for ($h = 8; $h <= 15; $h++) {
              foreach ([0, 30] as $m) {
                $timeSlots[] = str_pad($h, 2, '0', STR_PAD_LEFT) . ':' . str_pad($m, 2, '0', STR_PAD_LEFT);
              }
            }
          @endphp
          <select id="scheduled-time" name="scheduled_time" class="form-control mt-1 block w-full">
            <option value="">Selecciona hora</option>
            @foreach($timeSlots as $ts)
              <option value="{{ $ts }}" @if(isset($appointment) && $appointment->scheduled_at && (strpos($appointment->scheduled_at, 'T') !== false ? (new \Carbon\Carbon($appointment->scheduled_at))->format('H:i') : (explode(' ', $appointment->scheduled_at)[1] ?? '') ) == $ts) selected @endif>{{ $ts }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Empleado</label>
          <select id="employee-select" name="employee_id" class="form-control mt-1 block w-full">
            <option value="">Selecciona fecha/hora primero</option>
          </select>
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700">Notas adicionales</label>
          <textarea id="appt-notes" name="notes" rows="3" class="form-control mt-1 block w-full" placeholder="Opcional" @if($isReschedule) disabled @endif></textarea>
        </div>
      </div>

      <div class="mt-6">
          <div class="flex justify-center">
          <button type="submit" id="appt-submit" class="px-8 py-3 rounded-md text-white btn-green text-lg w-full md:w-1/3">@if($isReschedule) Reagendar cita @else Programar cita @endif</button>
        </div>
      </div>
    </form>
  </div>
</main>

<script>
  (function(){
  console.debug('formAppointment script loaded');
    const petApi = @json($apiPets);
    const svcApi = @json($apiServices);
    const vetsApi = @json($apiVets);
    const apptApi = @json($apiAppointments);

  const petSearch = document.getElementById('pet-search');
    const petResults = document.getElementById('pet-results');
    const petId = document.getElementById('pet-id');
    const clientDisplay = document.getElementById('client-display');
    const clientId = document.getElementById('client-id');

    const svcSearch = document.getElementById('service-search');
    const svcResults = document.getElementById('service-results');
    const svcId = document.getElementById('service-id');
    let svcPriceBySize = {};

  const sizeHidden = document.getElementById('appt-size');
  const sizeDisplay = document.getElementById('appt-size-display');

  function formatSizeLabel(s) {
    if (!s && s !== 0) return '';
    let str = String(s).trim().toLowerCase();
    return str.charAt(0).toUpperCase() + str.slice(1);
  }
  const priceInput = document.getElementById('appt-price');
  const weightInput = document.getElementById('appt-weight');
  const scheduledDate = document.getElementById('scheduled-date');
  const scheduledTime = document.getElementById('scheduled-time');
    const employeeSelect = document.getElementById('employee-select');
    const notesInput = document.getElementById('appt-notes');
    const form = document.getElementById('appointment-form');
    const submitBtn = document.getElementById('appt-submit');

    // Server-side prefill support: the route may pass an `appointment` object
    const serverPrefill = @json($appointment ?? null);
    // Reschedule mode detection (client-side or server-side)
    const params = new URLSearchParams(window.location.search);
    let rescheduleId = params.get('reschedule');
    let isReschedule = !!rescheduleId || !!serverPrefill;
    if (serverPrefill && serverPrefill.id) {
      rescheduleId = String(serverPrefill.id);
    }
    if (isReschedule) {
      // Adjust UI for reschedule
      const _h1 = document.querySelector('h1');
      if (_h1) _h1.textContent = 'Reagendar cita';
      submitBtn.textContent = 'Reagendar cita';
    }

    // Utilities
    function el(tag, cls, text){ const e = document.createElement(tag); if (cls) e.className = cls; if (text) e.textContent = text; return e; }
    function clearResults(container){ container.innerHTML=''; container.style.display='none'; }

    // Pet search: mirror behavior used in formPet's client search — ask the server on input/focus.
    let petTimer = null;

    // quick sanity log to ensure element exists and listeners are attached
    if (!petSearch) {
      console.warn('pet-search element not found on page');
    } else {
      console.debug('pet-search element found, attaching listeners');
    }

    petSearch.addEventListener('input', function(e){
      console.debug('pet-search input event, value="' + (e.target.value||'') + '"');
      const q = (e.target.value || '').trim();
      petId.value = '';
      clientDisplay.value = '';
      clientId.value = '';
      if (!q) { clearResults(petResults); return; }
      clearTimeout(petTimer);
      petTimer = setTimeout(()=> fetchPetByName(q), 250);
    });

    petSearch.addEventListener('focus', function(e){
      console.debug('pet-search focus event');
      const q = (e.target.value || '').trim();
      if (q) {
        clearTimeout(petTimer);
        petTimer = setTimeout(()=> fetchPetByName(q), 0);
      }
    });
    petSearch.addEventListener('click', function(e){
      console.debug('pet-search click event');
      const q = (e.target.value || '').trim();
      if (q) {
        clearTimeout(petTimer);
        petTimer = setTimeout(()=> fetchPetByName(q), 0);
      }
    });

    async function fetchPetByName(q){
      try {
        const url = petApi + '/' + encodeURIComponent(q);
        console.debug('fetchPetByName URL:', url);
        const res = await fetch(url, { headers: {'Accept':'application/json'}, credentials: 'same-origin' });
        if (res.status === 404) { petResults.innerHTML = '<div class="p-2 text-sm text-gray-500">No encontrado</div>'; petResults.style.display='block'; return; }
        if (!res.ok) { console.error('Error fetching pet', res.status); petResults.innerHTML = '<div class="p-2 text-sm text-red-500">Error al buscar mascota (código ' + res.status + ')</div>'; petResults.style.display='block'; return; }
        const payload = await res.json();
        // API may return single object or array; normalize to array
        const list = Array.isArray(payload) ? payload : (payload ? [payload] : []);
        if (!list.length) { petResults.innerHTML = '<div class="p-2 text-sm text-gray-500">No encontrado</div>'; petResults.style.display='block'; return; }
        renderPetResults(list);
      } catch (err) { console.error('Fetch pet failed', err); }
    }

    function renderPetResults(pets){
      petResults.innerHTML = '';
      pets.forEach(p => {
        // card-like result similar to client search UI
        const wrapper = document.createElement('div');
        wrapper.className = 'px-3 py-2 hover:bg-gray-50 cursor-pointer flex items-center gap-3 transition-colors duration-150';

        // avatar / initials (use pet name initials or client initials)
        const avatar = document.createElement('div');
        avatar.className = 'w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-sm font-semibold text-gray-700';
        const petInitial = ((p.name || '').charAt(0) || '').toUpperCase();
        const clientInitial = (p.client && (p.client.name || '') ? (p.client.name.charAt(0) || '') : '');
        avatar.textContent = (petInitial || clientInitial).toUpperCase();

        // main info
        const info = document.createElement('div');
        info.className = 'flex-1 min-w-0';
        const title = document.createElement('div');
        title.className = 'text-sm font-medium text-gray-900 truncate';
        title.textContent = (p.name ?? '-') + (p.breed && p.breed.name ? ' — ' + p.breed.name : '');
        const meta = document.createElement('div');
        meta.className = 'text-xs text-gray-500 truncate';
        const parts = [];
        if (p.client && p.client.email) parts.push(p.client.email);
        if (p.client && p.client.phone) parts.push(p.client.phone);
        meta.textContent = parts.join(' — ');

        info.appendChild(title);
        info.appendChild(meta);

        // chevron icon
        const chevron = document.createElement('div');
        chevron.className = 'text-gray-400';
        chevron.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>';

        wrapper.appendChild(avatar);
        wrapper.appendChild(info);
        wrapper.appendChild(chevron);

        wrapper.addEventListener('click', ()=>{
          petId.value = p.id;
          petSearch.value = p.name || '';
          if (p.client) {
            clientDisplay.value = (p.client.name||'') + (p.client.last_name_primary ? ' ' + p.client.last_name_primary : '');
            clientId.value = p.client.id ?? p.client_id ?? '';
          }
          // prefill size and weight
          if (p.size) {
            sizeHidden.value = p.size;
            sizeDisplay.value = formatSizeLabel(p.size);
            updatePriceFromSize();
          }
          if (p.weight !== undefined && p.weight !== null) {
            weightInput.value = p.weight;
          }
          clearResults(petResults);
        });

        petResults.appendChild(wrapper);
      });
      petResults.style.display='block';
    }

    // Service search
    let svcTimer = null;
    svcSearch.addEventListener('input', function(e){
      const q = (e.target.value || '').trim();
      svcId.value = '';
      svcPriceBySize = {};
      priceInput.value = '';
      if (!q) { clearResults(svcResults); return; }
      clearTimeout(svcTimer);
      svcTimer = setTimeout(()=> fetchServiceByName(q), 250);
    });

    async function fetchServiceByName(q){
      try {
        const url = svcApi + '/' + encodeURIComponent(q);
        console.debug('fetchServiceByName URL:', url);
        const res = await fetch(url, { headers: {'Accept':'application/json'}, credentials: 'same-origin' });
        if (res.status === 404) { svcResults.innerHTML = '<div class="p-2 text-sm text-gray-500">No encontrado</div>'; svcResults.style.display='block'; return; }
        if (!res.ok) { console.error('Error fetching service', res.status); svcResults.innerHTML = '<div class="p-2 text-sm text-red-500">Error al buscar servicio (código ' + res.status + ')</div>'; svcResults.style.display='block'; return; }
        const svc = await res.json();
        const list = Array.isArray(svc) ? svc : (svc ? [svc] : []);
        if (!list.length) { svcResults.innerHTML = '<div class="p-2 text-sm text-gray-500">No encontrado</div>'; svcResults.style.display='block'; return; }
        renderServiceResults(list);
      } catch (err) { console.error('Fetch service failed', err); }
    }

    function renderServiceResults(list){
      svcResults.innerHTML = '';
      list.forEach(s => {
        const wrapper = el('div','p-2 hover:bg-gray-50 cursor-pointer');
        wrapper.textContent = (s.name || '-') + (s.description ? ' — ' + s.description : '');
        wrapper.addEventListener('click', ()=>{
          svcId.value = s.id;
          svcSearch.value = s.name || '';
          svcPriceBySize = (s.price_by_size && typeof s.price_by_size === 'object') ? s.price_by_size : {};
          // update price according to current size
          updatePriceFromSize();
          clearResults(svcResults);
        });
        svcResults.appendChild(wrapper);
      });
      svcResults.style.display='block';
    }

    function updatePriceFromSize(){
      const sz = sizeHidden.value;
      if (!sz || !svcPriceBySize) { priceInput.value = ''; return; }
      const val = svcPriceBySize[sz];
      if (val === undefined || val === null || isNaN(Number(val))) { priceInput.value = ''; return; }
      priceInput.value = '$' + Number(val).toFixed(2);
    }

    // sizeHidden is updated programmatically when a pet/service is chosen; keep listener if manually changed elsewhere
    try { sizeHidden.addEventListener && sizeHidden.addEventListener('change', updatePriceFromSize); } catch(e){}

    // Fetch available vets when both date and time are set
    async function fetchVetsForDateTime() {
      const d = (scheduledDate && scheduledDate.value) ? scheduledDate.value : '';
      const t = (scheduledTime && scheduledTime.value) ? scheduledTime.value : '';
      employeeSelect.innerHTML = '<option>Buscando...</option>';
      if (!d || !t) { employeeSelect.innerHTML = '<option value="">Selecciona fecha/hora primero</option>'; return; }
      // send scheduled_at in server-local format 'YYYY-MM-DD HH:MM:SS'
      const local = d + ' ' + (t.length === 5 ? t : (t + ':00')) + ':00';
      try {
        const res = await fetch(vetsApi + '?scheduled_at=' + encodeURIComponent(local), { headers: {'Accept':'application/json'} });
        if (!res.ok) { employeeSelect.innerHTML = '<option value="">No hay veterinarios disponibles</option>'; return; }
        const list = await res.json();
        if (!Array.isArray(list) || !list.length) { employeeSelect.innerHTML = '<option value="">No hay veterinarios disponibles</option>'; return; }
        employeeSelect.innerHTML = '<option value="">-- Selecciona un veterinario --</option>';
        list.forEach(emp => {
          const opt = document.createElement('option');
          opt.value = emp.id;
          const label = (emp.name ?? emp.nombre ?? '') + (emp.last_name_primary ? ' ' + emp.last_name_primary : '');
          opt.textContent = label || (emp.email || ('Empleado ' + emp.id));
          employeeSelect.appendChild(opt);
        });
        // if rescheduling, preselect employee if provided
        if (isReschedule) {
          try { if (window._prefillEmployeeId) employeeSelect.value = window._prefillEmployeeId; } catch(e){}
        }
      } catch (err) { console.error('Failed to fetch vets', err); employeeSelect.innerHTML = '<option value="">Error al cargar veterinarios</option>'; }
    }

    // When date/time change, enable employee selection (if it was disabled for reschedule) and fetch available vets
    async function handleDateTimeChange() {
      try {
        // If in reschedule mode and employeeSelect is disabled (showing assigned employee), enable it so user can pick a different vet
        if (isReschedule && employeeSelect.disabled) {
          employeeSelect.disabled = false;
          employeeSelect.classList.remove('bg-gray-50','text-gray-600');
        }
      } catch(e) { console.warn('handleDateTimeChange enable error', e); }
      await fetchVetsForDateTime();
    }

    if (scheduledDate) scheduledDate.addEventListener('change', handleDateTimeChange);
    if (scheduledTime) scheduledTime.addEventListener('change', handleDateTimeChange);

    // If rescheduling, load appointment data and prefill form, then lock fields except date/time/employee
    if (isReschedule) {
      (async function(){
        try {
          console.debug('Reschedule mode, loading appointment', rescheduleId);
          if (serverPrefill) {
            console.debug('Using serverPrefill for appointment', serverPrefill);
            const a = serverPrefill;
            // serverPrefill available (no visible debug box) — log to console
            console.debug('serverPrefill', serverPrefill);
            // proceed to prefill using 'a'
            const a_prefill = a;
            // prefill pet
            if (a_prefill.pet) {
              petId.value = a_prefill.pet.id;
              petSearch.value = a_prefill.pet.name ?? '';
              if (a_prefill.pet.client) {
                clientDisplay.value = (a_prefill.pet.client.name||'') + (a_prefill.pet.client.last_name_primary ? ' ' + a_prefill.pet.client.last_name_primary : '');
                clientId.value = a_prefill.pet.client.id ?? a_prefill.pet.client_id ?? '';
              }
            }
            // prefill service
            if (a_prefill.service) {
              svcId.value = a_prefill.service.id;
              svcSearch.value = a_prefill.service.name ?? '';
              svcPriceBySize = (a_prefill.service.price_by_size && typeof a_prefill.service.price_by_size === 'object') ? a_prefill.service.price_by_size : {};
              updatePriceFromSize();
            }
            if (a_prefill.size) {
              sizeHidden.value = a_prefill.size;
              sizeDisplay.value = formatSizeLabel(a_prefill.size);
            } else if (a_prefill.pet && a_prefill.pet.size) {
              sizeHidden.value = a_prefill.pet.size;
              sizeDisplay.value = formatSizeLabel(a_prefill.pet.size);
            }
            updatePriceFromSize();
          
            if (a_prefill.price !== undefined && a_prefill.price !== null) priceInput.value = '$' + Number(a_prefill.price).toFixed(2);
            if (a_prefill.pet && a_prefill.pet.weight !== undefined && a_prefill.pet.weight !== null) weightInput.value = a_prefill.pet.weight;
            if (a_prefill.notes) notesInput.value = a_prefill.notes;
            if (a_prefill.employee_id) window._prefillEmployeeId = a_prefill.employee_id;
            // set scheduled date/time
            if (a_prefill.scheduled_at) {
              let dt = a_prefill.scheduled_at;
              let datePart = '';
              let timePart = '';
              if (dt.indexOf('T') !== -1) {
                const d = new Date(dt);
                const pad = n => String(n).padStart(2,'0');
                datePart = d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate());
                timePart = pad(d.getHours()) + ':' + pad(d.getMinutes());
              } else if (dt.indexOf(' ') !== -1) {
                const parts = dt.split(' ');
                datePart = parts[0];
                timePart = parts[1].slice(0,5);
              }
              if (datePart) scheduledDate.value = datePart;
              if (timePart) scheduledTime.value = timePart;
            }
            // lock fields: only date/time remain editable. Show as disabled (gray).
            [petSearch, svcSearch, sizeDisplay, priceInput, weightInput, notesInput].forEach(el => {
              try { el.disabled = true; el.classList.add('bg-gray-50', 'text-gray-600'); } catch(e){}
            });
            petResults.style.display = 'none'; svcResults.style.display = 'none';
            // show assigned employee and disable selection (user requested only date/time editable)
            try {
              let empId = a_prefill.employee_id ?? (a_prefill.employee ? (a_prefill.employee.id ?? '') : '');
              let empName = '';
              if (a_prefill.employee) empName = (a_prefill.employee.name ?? a_prefill.employee.nombre ?? '') + (a_prefill.employee.last_name_primary ? ' ' + a_prefill.employee.last_name_primary : '');
              empName = empName || (a_prefill.employee_name ?? 'Sin asignar');
              employeeSelect.innerHTML = '<option value="' + (empId ?? '') + '">' + empName + '</option>';
              employeeSelect.disabled = true; employeeSelect.classList.add('bg-gray-50','text-gray-600');
            } catch(e) { console.warn('Failed to set employee select', e); }
            return;
            return;
          }
          const fetchUrl = (apptApi.endsWith('/') ? apptApi.slice(0,-1) : apptApi) + '/' + encodeURIComponent(rescheduleId);
          console.debug('Fetching appointment URL:', fetchUrl);
          const res = await fetch(fetchUrl, { headers: {'Accept':'application/json'}, credentials: 'same-origin' });
          if (!res.ok) { console.error('Failed to load appointment', res.status); alert('No se pudo cargar la cita para reagendar (código ' + res.status + ')'); return; }
          const raw = await res.text();
          let resp;
          try { resp = JSON.parse(raw); } catch(e) { resp = raw; }
          console.debug('Raw appointment response:', resp);
          // log raw response to console (debug UI removed)
          console.debug('appointment response', resp);
          // support debug wrapper (controller may return { debug, appointment }) during troubleshooting
          if (resp && resp.debug) console.debug('getAppointment debug:', resp.debug);
          const a = (resp && resp.appointment) ? resp.appointment : resp;
          console.debug('Loaded appointment (normalized):', a);
          // prefill pet
          if (a.pet) {
            petId.value = a.pet.id;
            petSearch.value = a.pet.name ?? '';
            if (a.pet.client) {
              clientDisplay.value = (a.pet.client.name||'') + (a.pet.client.last_name_primary ? ' ' + a.pet.client.last_name_primary : '');
              clientId.value = a.pet.client.id ?? a.pet.client_id ?? '';
            }
          }
          // prefill service
          if (a.service) {
            svcId.value = a.service.id;
            svcSearch.value = a.service.name ?? '';
            svcPriceBySize = (a.service.price_by_size && typeof a.service.price_by_size === 'object') ? a.service.price_by_size : {};
            // update readonly price input according to size
            updatePriceFromSize();
          }
          // prefill size/weight/notes
          if (a.size) { sizeHidden.value = a.size; sizeDisplay.value = formatSizeLabel(a.size); updatePriceFromSize(); }
          if (a.price !== undefined && a.price !== null) priceInput.value = '$' + Number(a.price).toFixed(2);
          if (a.pet && a.pet.weight !== undefined && a.pet.weight !== null) weightInput.value = a.pet.weight;
          if (a.notes) notesInput.value = a.notes;

          try {
            console.debug('Appointment keys:', a ? Object.keys(a) : 'no-appointment');
            // prefill pet (support multiple shapes)
            const petObj = a.pet ?? (a.pet_id ? { id: a.pet_id, name: a.pet_name ?? '' } : null);
            if (petObj) {
              petId.value = petObj.id ?? '';
              petSearch.value = petObj.name ?? '';
              const clientObj = (petObj.client ?? a.client ?? a.pet_client ?? null);
              if (clientObj) {
                clientDisplay.value = (clientObj.name||'') + (clientObj.last_name_primary ? ' ' + clientObj.last_name_primary : '');
                clientId.value = clientObj.id ?? clientObj.client_id ?? '';
              }
            } else if (a.pet_id) {
              // fallback: set id directly
              petId.value = a.pet_id;
            }

            // prefill service
            const svcObj = a.service ?? (a.service_id ? { id: a.service_id, name: a.service_name ?? '' } : null);
            if (svcObj) {
              svcId.value = svcObj.id ?? '';
              svcSearch.value = svcObj.name ?? '';
              svcPriceBySize = (svcObj.price_by_size && typeof svcObj.price_by_size === 'object') ? svcObj.price_by_size : (a.service && a.service.price_by_size ? a.service.price_by_size : {});
              updatePriceFromSize();
            } else if (a.service_id) {
              svcId.value = a.service_id;
            }

            // prefill size/weight/notes
            if (a.size) { sizeHidden.value = a.size; sizeDisplay.value = formatSizeLabel(a.size); updatePriceFromSize(); }
            if (a.price !== undefined && a.price !== null) priceInput.value = '$' + Number(a.price).toFixed(2);
            const petWeight = (a.pet && (a.pet.weight !== undefined && a.pet.weight !== null)) ? a.pet.weight : (a.weight ?? null);
            if (petWeight !== undefined && petWeight !== null) weightInput.value = petWeight;
            if (a.notes) notesInput.value = a.notes;
          } catch (prefillErr) {
            console.error('Error during prefill processing', prefillErr, a);
          }
          if (a.employee_id) {
            // store temporarily until vets fetched
            window._prefillEmployeeId = a.employee_id;
          }

          // lock fields: only date/time should be editable (user requested)
          [petSearch, svcSearch, sizeDisplay, priceInput, weightInput, notesInput].forEach(el => {
            try { el.disabled = true; el.classList.add('bg-gray-50', 'text-gray-600'); } catch(e){}
          });
          // hide search results if visible
          petResults.style.display = 'none'; svcResults.style.display = 'none';
          // set employee select to the assigned employee and disable it
          try {
            const empObj = a.employee ?? null;
            const empId = a.employee_id ?? (empObj ? (empObj.id ?? '') : '');
            const empName = empObj ? ((empObj.name ?? empObj.nombre ?? '') + (empObj.last_name_primary ? ' ' + empObj.last_name_primary : '')) : (a.employee_name ?? 'Sin asignar');
            employeeSelect.innerHTML = '<option value="' + (empId ?? '') + '">' + empName + '</option>';
          } catch(e) { console.warn('Failed to set employee select', e); }

        } catch (err) { console.error('Error pre-filling appointment', err); }
      })();
    }

    // Submit form
    form.addEventListener('submit', async function(e){
      e.preventDefault();
      submitBtn.disabled = true; submitBtn.classList.add('opacity-70');
      // basic validation (in reschedule mode we only require date/time)
      if (!isReschedule) {
        if (!petId.value) { if (window.showToast) window.showToast('Selecciona una mascota', { type: 'error' }); else alert('Selecciona una mascota'); submitBtn.disabled = false; submitBtn.classList.remove('opacity-70'); return; }
        if (!svcId.value) { if (window.showToast) window.showToast('Selecciona un servicio', { type: 'error' }); else alert('Selecciona un servicio'); submitBtn.disabled = false; submitBtn.classList.remove('opacity-70'); return; }
      }
      if (!scheduledDate.value || !scheduledTime.value) { if (window.showToast) window.showToast('Selecciona fecha y hora', { type: 'error' }); else alert('Selecciona fecha y hora'); submitBtn.disabled = false; submitBtn.classList.remove('opacity-70'); return; }

      const payload = new FormData();
      payload.append('pet_id', petId.value);
      payload.append('service_id', svcId.value);
      const emp = employeeSelect.value;
      if (emp) payload.append('employee_id', emp);
      // server expects scheduled_at parseable — send ISO
    // send scheduled_at as server-local datetime string to avoid UTC conversion issues
    const d = scheduledDate.value;
    const t = scheduledTime.value;
    const scheduledLocal = d + ' ' + (t.length === 5 ? t : (t + ':00')) + ':00';
    payload.append('scheduled_at', scheduledLocal);
  const sz = sizeHidden.value; if (sz) payload.append('size', sz);
      const notes = notesInput.value; if (notes) payload.append('notes', notes);

      try {
        const headers = { 'Accept':'application/json' };
  const _tokenEl = document.querySelector('input[name="_token"]');
  const token = _tokenEl ? _tokenEl.value : null;
  if (token) headers['X-CSRF-TOKEN'] = token;
        let res;
        if (isReschedule) {
          // Some PHP runtimes do not populate PUT form-data reliably. Use POST with _method=PUT override so Laravel receives form fields.
          const fd = new FormData();
          const d = scheduledDate.value;
          const t = scheduledTime.value;
          const scheduledLocal = d + ' ' + (t.length === 5 ? t : (t + ':00')) + ':00';
          fd.append('scheduled_at', scheduledLocal);
          const emp = employeeSelect.value; if (emp) fd.append('employee_id', emp);
          // method override
          fd.append('_method', 'PUT');
          res = await fetch(apptApi + '/' + encodeURIComponent(rescheduleId) + '/reschedule', { method: 'POST', headers, body: fd, credentials: 'same-origin' });
          if (res.status === 200) {
            if (window.showToast) window.showToast('Cita reagendada', { type: 'success' });
            window.location.href = '{{ url("/dashboard/citas") }}';
            return;
          }
        } else {
          res = await fetch(apptApi, { method: 'POST', headers, body: payload, credentials: 'same-origin' });
          if (res.status === 201) {
            if (window.showToast) window.showToast('Cita programada', { type: 'success' });
            window.location.href = '{{ url("/dashboard/citas") }}';
            return;
          }
        }
        if (res.status === 422) {
          const payload = await res.json();
          if (payload && payload.errors) {
            // show first error via toast
            const first = Object.values(payload.errors)[0];
            const msg = Array.isArray(first) ? first.join('\n') : first;
            if (window.showToast) window.showToast(msg, { type: 'error' }); else alert(msg);
          } else if (payload && payload.error) {
            if (window.showToast) window.showToast(payload.error, { type: 'error' }); else alert(payload.error);
          }
          submitBtn.disabled = false; submitBtn.classList.remove('opacity-70');
          return;
        }
        const txt = await res.text().catch(()=>null);
        if (window.showToast) window.showToast('Error: ' + (txt || res.status), { type: 'error' }); else alert('Error: ' + (txt || res.status));
  } catch (err) { console.error(err); if (window.showToast) window.showToast('Error de red', { type: 'error' }); else alert('Error de red'); }
      finally { submitBtn.disabled = false; submitBtn.classList.remove('opacity-70'); }
    });

  })();
</script>
