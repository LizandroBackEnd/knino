@php
	use App\Models\Appointment;
	use App\Models\enums\StatusEnum;
	use Carbon\Carbon;

	$appointments = Appointment::with(['pet.client','service','employee'])->orderBy('scheduled_at','asc')->get();
	// Auto-expire past appointments that are not completed or canceled
	foreach ($appointments as $ap) {
		if ($ap->scheduled_at instanceof \Illuminate\Support\Carbon && $ap->scheduled_at->lt(Carbon::now())) {
			if (!in_array($ap->status, [StatusEnum::COMPLETED->value, StatusEnum::CANCELADA->value, StatusEnum::EXPIRED->value])) {
				$ap->status = StatusEnum::EXPIRED->value;
				$ap->save();
			}
		}
	}

	function formatTime($dt) { return $dt ? $dt->format('H:i') : '-'; }
	function fullname($u) { if (!$u) return '-'; return trim(($u->name ?? $u->nombre ?? '') . ' ' . ($u->last_name_primary ?? '')); }
	function normalizeStatus($s) {
		$raw = strtolower(trim((string)($s ?? '')));
		// Map common English values to enum Spanish values
		return match ($raw) {
			'scheduled', 'confirmed' => StatusEnum::SCHEDULED->value,
			'rescheduled', 'reschedule', 'reprogrammed' => StatusEnum::REPROGRAMADA->value,
			'canceled', 'cancelled' => StatusEnum::CANCELADA->value,
			'completed' => StatusEnum::COMPLETED->value,
			'expired' => StatusEnum::EXPIRED->value,
			default => $raw ?: StatusEnum::SCHEDULED->value,
		};
	}

	function statusLabel($s) {
		$norm = normalizeStatus($s);
		return match ($norm) {
			StatusEnum::SCHEDULED->value => 'Programada',
			StatusEnum::REPROGRAMADA->value => 'Reprogramada',
			StatusEnum::CANCELADA->value => 'Cancelada',
			StatusEnum::EXPIRED->value => 'Expirada',
			StatusEnum::COMPLETED->value => 'Completada',
			default => ucfirst($norm),
		};
	}

	function statusClass($s) {
		$norm = normalizeStatus($s);
		return match ($norm) {
			// Programada -> azul
			StatusEnum::SCHEDULED->value => 'inline-block px-2 py-1 rounded-full bg-blue-100 text-blue-700',
			// Reprogramada -> amarillo
			StatusEnum::REPROGRAMADA->value => 'inline-block px-2 py-1 rounded-full bg-yellow-100 text-yellow-700',
			// Completada -> verde
			StatusEnum::COMPLETED->value => 'inline-block px-2 py-1 rounded-full bg-green-100 text-green-700',
			// Cancelada -> rojo
			StatusEnum::CANCELADA->value => 'inline-block px-2 py-1 rounded-full bg-red-100 text-red-700',
			// Expirada -> morado
			StatusEnum::EXPIRED->value => 'inline-block px-2 py-1 rounded-full bg-purple-100 text-purple-700',
			default => 'inline-block px-2 py-1 rounded-full bg-gray-100 text-gray-700',
		};
	}
@endphp

<div id="appointments-cards" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
	@if($appointments->isEmpty())
		<div class="text-sm text-gray-500">No hay citas programadas</div>
	@else
		@foreach($appointments as $a)
			@php
				$time = $a->scheduled_at;
				$pet = $a->pet;
				$client = $a->client ?? ($pet ? $pet->client : null);
				$service = $a->service;
				$emp = $a->employee;
				$st = $a->status ?? StatusEnum::SCHEDULED->value;
			@endphp
			<div class="bg-white rounded-lg shadow overflow-hidden border-l-4 border-green-400" data-appt-status="{{ $st }}">
				<div class="p-4 flex items-start">
					<div class="flex-shrink-0 mr-4">
						<div class="bg-green-500 text-white rounded-md px-3 py-2 text-center">
							<div class="text-xs">Hora</div>
							<div class="text-lg font-bold">{{ $time ? $time->format('H:i') : '--:--' }}</div>
						</div>
					</div>

					<div class="flex-1">
						<div class="flex items-center justify-between">
							<div>
								<div class="font-semibold text-xl">{{ $pet?->name ?? '—' }}</div>
								<div class="text-sm text-gray-600 flex items-center mt-1">
									<img src="/icons/clients.svg" alt="Cliente" class="w-4 h-4 mr-2 opacity-80"> {{ $client ? fullname($client) : '—' }}
								</div>
								<div class="mt-3 text-sm text-gray-700 flex items-center">
									<img src="/icons/services.svg" alt="Servicio" class="w-4 h-4 mr-2 opacity-80"> {{ $service?->name ?? '—' }}
								</div>
								<div class="mt-2 text-sm text-gray-700 flex items-center">
									<img src="/icons/employees.svg" alt="Empleado" class="w-4 h-4 mr-2 opacity-80"> {{ $emp ? fullname($emp) : 'Sin asignar' }}
								</div>
							</div>

											<div class="ml-4 flex-shrink-0 text-sm">
												<span data-appt-id="{{ $a->id }}" class="status-badge {{ statusClass($st) }}">{{ statusLabel($st) }}</span>
											</div>
						</div>

						<div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
							@if((($st ?? '') !== \App\Models\enums\StatusEnum::COMPLETED->value))
							<a href="{{ url('/dashboard/citas/create') }}?reschedule={{ $a->id }}" data-id="{{ $a->id }}" class="flex items-center justify-center px-3 py-2 bg-white border border-gray-200 rounded-md text-sm text-gray-700 hover:bg-gray-50 shadow-sm transition-colors duration-150" data-nav>
								<span class="inline-flex w-7 h-7 mr-2 items-center justify-center bg-gray-100 rounded-full"><img src="/icons/edit.svg" alt="Reagendar" class="w-3 h-3" /></span>
								Reagendar cita
							</a>
							@endif

							{{-- Completar cita button (hidden when already completed or canceled) --}}
							@if((($st ?? '') !== \App\Models\enums\StatusEnum::CANCELADA->value) && (($st ?? '') !== \App\Models\enums\StatusEnum::COMPLETED->value))
							<div class="flex items-center justify-center">
								<button type="button" class="btn-complete flex items-center justify-center px-3 py-2 bg-white border border-green-100 rounded-md text-sm hover:bg-green-50 shadow-sm transition-colors duration-150" data-id="{{ $a->id }}">
									<span class="inline-flex w-7 h-7 mr-2 items-center justify-center bg-green-100 rounded-full"><img src="/icons/checks.svg" alt="Completar" class="w-3 h-3" /></span>
									<span class="text-green-700 font-medium">Completar cita</span>
								</button>
							</div>
							@endif

							{{-- Cancelar in right column (hidden if already canceled) --}}
							@if((($st ?? '') !== \App\Models\enums\StatusEnum::CANCELADA->value) && (($st ?? '') !== \App\Models\enums\StatusEnum::COMPLETED->value))
							<div class="flex items-center justify-center">
								<button type="button" class="btn-cancel flex items-center justify-center px-3 py-2 bg-white border border-red-100 rounded-md text-sm hover:bg-red-50 shadow-sm transition-colors duration-150" data-id="{{ $a->id }}">
									<span class="inline-flex w-7 h-7 mr-2 items-center justify-center bg-red-100 rounded-full"><img src="/icons/trash.svg" alt="Cancelar" class="w-3 h-3" /></span>
									<span class="text-red-700 font-medium">Cancelar cita</span>
								</button>
							</div>
							@endif
						</div>
					</div>
				</div>
			</div>
		@endforeach
	@endif
</div>

<script>
(function(){
	const container = document.getElementById('appointments-cards');

	// Cancel buttons: use the app-wide confirmDelete if available
	container.querySelectorAll('.btn-cancel').forEach(btn => {
		btn.addEventListener('click', async function(){
			const id = this.getAttribute('data-id');
			const ok = window.confirmDelete ? await window.confirmDelete('¿Cancelar esta cita?', { title: 'Confirmar cancelación', confirmText: 'Cancelar' }) : confirm('¿Cancelar esta cita?');
			if (!ok) return;
			try {
				const _tokenEl = document.querySelector('input[name="_token"]');
				const token = _tokenEl ? _tokenEl.value : null;
				const headers = {};
				if (token) headers['X-CSRF-TOKEN'] = token;
				const res = await fetch('/api/appointments/' + encodeURIComponent(id) + '/cancel', { method: 'POST', headers, credentials: 'same-origin' });
						if (res.ok) {
							if (window.showToast) window.showToast('Cita cancelada', { type: 'success' });
							// update status badge on the card to Cancelada
							const statusBadge = document.querySelector('.status-badge[data-appt-id="' + id + '"]');
							if (statusBadge) {
								statusBadge.textContent = 'Cancelada';
								statusBadge.className = 'status-badge inline-block px-2 py-1 rounded-full bg-red-100 text-red-700';
							}
							// Remove action buttons except 'Reagendar' so only re-schedule remains
							const toRemove = document.querySelectorAll('.btn-complete[data-id="' + id + '"], .btn-cancel[data-id="' + id + '"]');
							toRemove.forEach(el => el.remove());
							return;
						}
				const txt = await res.text().catch(()=>null);
				if (window.showToast) window.showToast('No se pudo cancelar la cita', { type: 'error' });
				console.error('Cancel failed', res.status, txt);
			} catch (err) { console.error(err); if (window.showToast) window.showToast('Error de red', { type: 'error' }); }
		});
	});

	// Complete buttons: mark appointment as completed
	container.querySelectorAll('.btn-complete').forEach(btn => {
					btn.addEventListener('click', async function(){
						const id = this.getAttribute('data-id');
			try {
				const _tokenEl = document.querySelector('input[name="_token"]');
				const token = _tokenEl ? _tokenEl.value : null;
				const headers = {};
				if (token) headers['X-CSRF-TOKEN'] = token;
				const res = await fetch('/api/appointments/' + encodeURIComponent(id) + '/complete', { method: 'POST', headers, credentials: 'same-origin' });
					if (res.ok) {
						// Show success toast
						if (window.showToast) window.showToast('Cita completada', { type: 'success' });
						// Update status badge to Completada (green)
						const statusBadge = document.querySelector('.status-badge[data-appt-id="' + id + '"]');
						if (statusBadge) {
							statusBadge.textContent = 'Completada';
							statusBadge.className = 'status-badge inline-block px-2 py-1 rounded-full bg-green-100 text-green-700';
						}
						// Remove all action buttons/links for this appointment (reagendar, completar, cancelar)
						const toRemove = document.querySelectorAll('.btn-complete[data-id="' + id + '"], .btn-cancel[data-id="' + id + '"], a[data-id="' + id + '"]');
						toRemove.forEach(el => el.remove());
						return;
					}
				const txt = await res.text().catch(()=>null);
				if (window.showToast) window.showToast('No se pudo completar la cita', { type: 'error' });
				console.error('Complete failed', res.status, txt);
			} catch (err) { console.error(err); if (window.showToast) window.showToast('Error de red', { type: 'error' }); }
		});
	});
})();
</script>
