@extends('layouts.dashboard')

@section('content')
  @if(!empty($showCreate))
    @include('components.forms.formEmployee')
  @else
  <main class="p-6" style="font-family: var(--font-secondary);">
    <div class="flex items-start justify-between mb-6">
      <div>
        <h1 class="text-3xl font-extrabold mb-1" style="font-family: var(--font-primary); color: var(--text-title);">Empleados</h1>
        <p class="text-sm text-gray-600">Gestiona la información de los empleados</p>
      </div>

      <div class="ml-4">
        <a href="{{ route('dashboard.empleados.create') }}" class="inline-flex items-center px-4 py-2 rounded-md text-white btn-green" data-nav style="background: var(--green);">
          {{-- Icono +: inline SVG from public/icons so it can inherit color (white). Fallback to inline SVG using currentColor. --}}
          @if(file_exists(public_path('icons/plus.svg')))
            @php
              $plus = file_get_contents(public_path('icons/plus.svg'));
              // remove width/height/class attributes so SVG can scale and inherit color
              $plus = preg_replace('/\s(width|height|class)="[^"]*"/i', '', $plus);
              // remove the invisible bbox path that some svgs include (M0 0h24v24H0z)
              $plus = preg_replace('/<path[^>]*d="M0 0h24v24H0z"[^>]*\/?>/i', '', $plus);
              // ensure the svg preserves aspect ratio and is centered
              $plus = preg_replace('/<svg(.*?)>/i', '<svg$1 preserveAspectRatio="xMidYMid meet">', $plus, 1);
            @endphp
            <span class="inline-flex items-center justify-center w-4 h-4 mr-2 text-white icon-inline" aria-hidden="true">{!! $plus !!}</span>
          @else
            <svg class="w-4 h-4 mr-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"/></svg>
          @endif
          <span>Agregar Empleado</span>
        </a>
      </div>
    </div>

    <div class="mb-6">
      <label for="search" class="sr-only">Buscar</label>
      <div class="relative">
        {{-- Posiciona el icono dentro del input: absolute left center --}}
        <div class="search-icon pointer-events-none absolute inset-y-0 left-3 flex items-center">
          {{-- Icono búsqueda: inline SVG from public/icons so it can inherit color (use text-gray-400). Fallback to inline SVG using currentColor. --}}
          @if(file_exists(public_path('icons/search.svg')))
            @php
              $search = file_get_contents(public_path('icons/search.svg'));
              $search = preg_replace('/\s(width|height|class)="[^"]*"/i', '', $search);
              // remove the invisible bbox path that some svgs include (M0 0h24v24H0z)
              $search = preg_replace('/<path[^>]*d="M0 0h24v24H0z"[^>]*\/?>/i', '', $search);
              // ensure centered rendering inside its viewBox
              $search = preg_replace('/<svg(.*?)>/i', '<svg$1 preserveAspectRatio="xMidYMid meet">', $search, 1);
            @endphp
            <span class="inline-flex items-center justify-center w-5 h-5 text-gray-400 icon-inline" aria-hidden="true">{!! $search !!}</span>
          @else
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 10a7 7 0 1 0 14 0a7 7 0 1 0 -14 0M21 21l-6-6"/></svg>
          @endif
          {{-- Si quieres usar otro nombre de archivo, cambia 'search.svg' por el nombre correcto en public/icons --}}
        </div>
        <input id="search" name="search" type="search" placeholder="Buscar por nombre del empleado" class="w-full pl-12 pr-4 py-3 rounded-lg border" style="background: var(--bg-input); border-color: var(--border);">
      </div>
    </div>

    <!-- Content area (cards / table) -->
    <div>
      {{-- Aquí irá la lista de empleados o componentes --}}
      @include('components.lists.listEmployee')
    </div>
    </div>
  </main>
  @endif
@endsection
