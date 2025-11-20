<aside class="w-72 h-screen bg-white text-gray-800 flex flex-col justify-between" style="font-family: var(--font-subtitle); box-shadow: -6px 0 12px -8px rgba(0,0,0,0.15), 6px 0 12px -8px rgba(0,0,0,0.15);">
  <div>
    <div class="px-6 py-6">
      <div class="flex items-center gap-3">
        <img src="/logos/icon.png" alt="K-NINO" class="w-12 h-12">
        <div>
          <div class="text-sm font-bold" style="font-family: var(--font-title); color: var(--color-primary);">K-NINO</div>
          <div class="text-xs text-gray-500" style="font-family: var(--font-subtitle);">Admin</div>
        </div>
      </div>
      <div class="mt-4 border-t border-gray-200"></div>

      <nav class="mt-4">
        @php
          $items = [
            ['Dashboard', 'dashboard', 'dashboard.svg'],
            ['Clientes', 'clientes', 'clients.svg'],
            ['Mascotas', 'mascotas', 'pets.svg'],
            ['Servicios', 'servicios', 'services.svg'],
            ['Empleados', 'empleados', 'employees.svg'],
            ['Usuarios', 'usuarios', 'users-plus.svg'],
          ];
        @endphp

        <div class="mt-2">
            @foreach($items as [$label, $route, $icon])
            @php
              $map = [
                'dashboard' => 'dashboard.home',
                'clientes' => 'dashboard.clientes',
                'mascotas' => 'dashboard.mascotas',
                'servicios' => 'dashboard.servicios',
                'empleados' => 'dashboard.empleados',
                'usuarios' => 'dashboard.users',
              ];
              $named = isset($map[$route]) ? $map[$route] : $route;
              $url = \Illuminate\Support\Facades\Route::has($named) ? route($named) : '#';
              $isActive = optional(request()->route())->getName() === $named;
            @endphp

            @php
              $extraAttr = '';
              // mark certain routes as admin-only in the UI
              if (in_array($route, ['empleados','servicios','usuarios'])) {
                $extraAttr = ' data-role="admin"';
              }
            @endphp
            <a href="{{ $url }}" {!! $extraAttr !!} class="flex items-center gap-3 px-4 py-3 mb-2 rounded-lg transition-colors w-full {{ $isActive ? 'bg-[var(--color-primary)] text-white' : 'text-gray-700 hover:bg-gray-50' }}" style="font-family: var(--font-subtitle);">
              <img src="/icons/{{ $icon }}" alt="{{ $label }} icon" class="w-5 h-5 {{ $isActive ? 'filter brightness-0 invert' : '' }}">
              <span class="flex-1 font-semibold text-sm" style="font-family: var(--font-title);">{{ $label }}</span>
            </a>
          @endforeach
        </div>
      </nav>
    </div>
  </div>

  <div class="px-4 py-4">
    <div class="mb-3 border-t border-gray-200"></div>

    <a href="#" id="logout-link" class="flex items-center gap-3 text-red-500 hover:text-red-700" style="font-family: var(--font-subtitle);">
        @if(file_exists(public_path('icons/logout.svg')))
            {!! file_get_contents(public_path('icons/logout.svg')) !!}
        @else
            <svg class="w-5 h-5 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"/></svg>
        @endif
        <span class="font-medium">Cerrar Sesión</span>
    </a>
  </div>
</aside>
