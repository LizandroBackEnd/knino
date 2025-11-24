@extends('layouts.dashboard')

@section('content')
	@if(!empty($showCreate))
		@include('components.forms.formAppointment')
	@else
	<main class="p-6" style="font-family: var(--font-secondary);">
		<div class="flex items-start justify-between mb-6">
			<div>
				<h1 class="text-3xl font-extrabold mb-1" style="font-family: var(--font-primary); color: var(--text-title);">Programar cita</h1>
				<p class="text-sm text-gray-600">Agenda y gestiona las citas de tus clientes</p>
			</div>

			<div class="ml-4 flex items-center space-x-4">
				{{-- Filter icon + status filters --}}
				<div class="inline-flex items-center bg-white rounded px-3 py-2 shadow-md" style="box-shadow: 0 6px 18px rgba(0,0,0,0.08);">
					@if(file_exists(public_path('icons/filter.svg')))
						@php
							$filterSvg = file_get_contents(public_path('icons/filter.svg'));
							$filterSvg = preg_replace('/\s(width|height|class)="[^"]*"/i', '', $filterSvg);
							$filterSvg = preg_replace('/<path[^>]*d="M0 0h24v24H0z"[^>]*\/?>>/i', '', $filterSvg);
							$filterSvg = preg_replace('/<svg(.*?)>/i', '<svg$1 preserveAspectRatio="xMidYMid meet">', $filterSvg, 1);
						@endphp
						<span class="inline-flex items-center justify-center w-5 h-5 mr-3 icon-inline" aria-hidden="true">{!! $filterSvg !!}</span>
					@else
						<img src="/icons/filter.svg" class="w-5 h-5 mr-3" alt="Filtrar">
					@endif
					<div id="status-filters" class="flex items-center space-x-3 justify-center">
						@php
							$filters = [
								['key' => \App\Models\enums\StatusEnum::SCHEDULED->value, 'label' => 'Programada', 'class' => 'bg-blue-100 text-blue-700'],
								['key' => \App\Models\enums\StatusEnum::REPROGRAMADA->value, 'label' => 'Reprogramada', 'class' => 'bg-yellow-100 text-yellow-700'],
								['key' => \App\Models\enums\StatusEnum::CANCELADA->value, 'label' => 'Cancelada', 'class' => 'bg-red-100 text-red-700'],
								['key' => \App\Models\enums\StatusEnum::EXPIRED->value, 'label' => 'Expirada', 'class' => 'bg-purple-100 text-purple-700'],
								['key' => \App\Models\enums\StatusEnum::COMPLETED->value, 'label' => 'Completada', 'class' => 'bg-green-100 text-green-700'],
							];
						@endphp
						<button data-filter="all" class="filter-pill inline-flex items-center justify-center text-center px-3 py-1 rounded text-sm bg-gray-50 border">Todos</button>
						@foreach($filters as $f)
							<button data-filter="{{ $f['key'] }}" class="filter-pill inline-flex items-center justify-center text-center px-3 py-1 rounded text-sm border {{ $f['class'] }}"> <span class="w-3 h-3 mr-2 rounded-full {{ $f['class'] }}" style="width:12px;height:12px"></span> {{ $f['label'] }}</button>
						@endforeach
					</div>
				</div>
				{{-- Programar cita button (hidden for veterinarians; accept english/spanish aliases) --}}
				{{-- Programar cita button: visibility handled in client-side JS --}}
				@php
					$userRole = auth()->check() ? strtolower(auth()->user()->role ?? '') : '';
					$isVet = in_array($userRole, ['veterinarian','veterinario','vet'], true);
				@endphp
				<button id="create-appointment-btn" data-href="{{ route('dashboard.citas.create') }}" type="button" @if($isVet) disabled aria-disabled="true" title="No estás autorizado" class="inline-flex items-center px-4 py-2 rounded-md text-white btn-green opacity-50 cursor-not-allowed" @else class="inline-flex items-center px-4 py-2 rounded-md text-white btn-green" @endif style="background: var(--green);">
					<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
					Programar cita
				</button>
				<script>
				// Role-based visual state: grey-out and disable the button for veterinarian roles.
				(function(){
					try {
						const btn = document.getElementById('create-appointment-btn');
						const vetAliases = ['veterinarian','veterinario','vet','veterinarians','veterinarios'];
						if (!btn) return;

						// Server-side role check: enable or disable button at render time.
						(function(){
							const btn = document.getElementById('create-appointment-btn');
							if (!btn) return;
							const isVet = @json($isVet);
							if (isVet) {
								// defensive: ensure disabled visual state
								btn.classList.add('opacity-50','cursor-not-allowed');
								btn.disabled = true;
								btn.setAttribute('aria-disabled','true');
								// silent click - do nothing
								btn.addEventListener('click', function(e){ e.preventDefault(); });
							} else {
								btn.disabled = false;
								btn.classList.remove('opacity-50','cursor-not-allowed');
								btn.addEventListener('click', function(){ const href = btn.getAttribute('data-href'); if (href) window.location = href; });
							}
						})();
					} catch(e) { console.warn('create appointment role guard', e); }
				})();
				</script>
			</div>
		</div>

		<div class="mb-6">
			<label for="search" class="sr-only">Buscar</label>
			<div class="relative">
				<div class="search-icon pointer-events-none absolute inset-y-0 left-3 flex items-center">
					@if(file_exists(public_path('icons/search.svg')))
						@php
							$search = file_get_contents(public_path('icons/search.svg'));
							$search = preg_replace('/\s(width|height|class)="[^"]*"/i', '', $search);
							$search = preg_replace('/<path[^>]*d="M0 0h24v24H0z"[^>]*\/?>/i', '', $search);
							$search = preg_replace('/<svg(.*?)>/i', '<svg$1 preserveAspectRatio="xMidYMid meet">', $search, 1);
						@endphp
						<span class="inline-flex items-center justify-center w-5 h-5 text-gray-400 icon-inline" aria-hidden="true">{!! $search !!}</span>
					@else
						<svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 10a7 7 0 1 0 14 0a7 7 0 1 0 -14 0M21 21l-6-6"/></svg>
					@endif
				</div>
				<input id="search" name="search" type="search" placeholder="Buscar por nombre de la mascota" class="w-full pl-12 pr-4 py-3 rounded-lg border" style="background: var(--bg-input); border-color: var(--border);">
			</div>
		</div>

		<div>
			@include('components.lists.listAppointment')
		</div>

		<script>
		// Search by pet name using the API endpoint GET /api/pets/{name}
		(function(){
			const searchEl = document.getElementById('search');
			const container = document.getElementById('appointments-cards');
			if (!searchEl || !container) return;

			function showAll() {
				Array.from(container.children).forEach(c => c.style.display = '');
				const noEl = document.getElementById('no-search-results'); if (noEl) noEl.remove();
			}

			function showNoResults() {
				Array.from(container.children).forEach(c => c.style.display = 'none');
				if (!document.getElementById('no-search-results')) {
					const el = document.createElement('div');
					el.id = 'no-search-results';
					el.className = 'text-sm text-gray-500';
					el.textContent = 'No se encontraron mascotas';
					container.parentNode.insertBefore(el, container);
				}
			}

			function filterByNames(names) {
				const cards = Array.from(container.children);
				if (!names || names.length === 0) { showNoResults(); return; }
				cards.forEach(c => {
					const petNameEl = c.querySelector('.font-semibold');
					const petName = petNameEl ? petNameEl.textContent.trim().toLowerCase() : '';
					if (names.includes(petName)) c.style.display = '';
					else c.style.display = 'none';
				});
				const noEl = document.getElementById('no-search-results'); if (noEl) noEl.remove();
			}

			let debounce;
			searchEl.addEventListener('input', function(){
				clearTimeout(debounce);
				const q = this.value.trim();
				debounce = setTimeout(async () => {
					if (!q) { showAll(); return; }
					try {
						const res = await fetch('/api/pets/' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
						if (!res.ok) { showNoResults(); return; }
						const data = await res.json();
						let names = [];
						if (Array.isArray(data)) names = data.map(p => String(p.name || p.nombre || '').trim().toLowerCase()).filter(Boolean);
						else if (data && (data.name || data.nombre)) names = [String(data.name || data.nombre).trim().toLowerCase()];
						if (names.length === 0) { showNoResults(); return; }
						filterByNames(names);
					} catch (err) {
						console.warn('search pets failed', err);
						showNoResults();
					}
				}, 300);
			});
		})();

		(function(){
			const container = document.getElementById('appointments-cards');
			const pills = document.querySelectorAll('#status-filters .filter-pill');
			function applyFilter(key){
				const cards = container ? Array.from(container.children) : [];
				cards.forEach(c => {
					const st = c.getAttribute('data-appt-status') || '';
					if (key === 'all' || !key) { c.style.display = ''; return; }
					if (st === key) c.style.display = ''; else c.style.display = 'none';
				});
			}
			pills.forEach(p => p.addEventListener('click', function(){
				const k = this.getAttribute('data-filter');
				// toggle active class
				document.querySelectorAll('#status-filters .filter-pill').forEach(x => x.classList.remove('ring','ring-2'));
				this.classList.add('ring','ring-2');
				applyFilter(k);
			}));
		})();
		</script>
	</main>
	@endif
@endsection

