<header class="flex items-center justify-between bg-white text-gray-900 px-6 py-3 shadow">
  <div>
    <h1 class="text-2xl font-bold tracking-tight" style="font-family: var(--font-title);">
      {{ $title ?? 'Sistema de Gestión Veterinaria' }}
    </h1>
  </div>

    <div class="flex items-center gap-4">
      <div class="text-right">
        <div class="text-sm font-medium" style="font-family: var(--font-subtitle);">Lizandro Antonio</div>
        <div class="text-xs text-gray-500" style="font-family: var(--font-subtitle);">Admin</div>
      </div>

      <img src="{{ asset($avatar ?? '/images/user.jpg') }}" alt="Avatar" class="w-9 h-9 rounded-full object-cover border-2 border-white shadow-sm">
    </div>
  
</header>
</header>
