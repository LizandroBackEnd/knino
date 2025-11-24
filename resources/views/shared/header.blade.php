<header class="flex items-center justify-between bg-white text-gray-900 px-6 py-3 shadow">
  <div>
    <h1 class="text-2xl font-bold tracking-tight" style="font-family: var(--font-title);">
      {{ $title ?? 'Sistema de Gestión Veterinaria' }}
    </h1>
  </div>

    <div class="flex items-center gap-4">
      <div class="text-right">
  <div class="text-sm font-medium" id="header-username" style="font-family: var(--font-subtitle);">{{ optional(auth()->user())->full_name ?? optional(auth()->user())->name ?? 'Invitado' }}</div>
      </div>
    </div>

</header>
