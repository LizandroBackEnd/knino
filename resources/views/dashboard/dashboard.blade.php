@extends('layouts.dashboard')

@section('content')
  <main class="p-6" style="font-family: var(--font-secondary);">
    <!-- Aquí va el contenido del dashboard -->
    <h2 class="text-lg font-bold mb-4" style="font-family: var(--font-primary);">
      Bienvenido al sistema K-NINO
    </h2>

    <!-- Ejemplo de estadísticas -->
    <div class="grid grid-cols-2 gap-6">
      <div class="bg-white p-4 rounded shadow">
        <p class="text-sm text-gray-600">Citas Atendidas</p>
        <h3 class="text-2xl font-bold text-primary">156</h3>
      </div>
      <div class="bg-white p-4 rounded shadow">
        <p class="text-sm text-gray-600">Servicios Realizados</p>
        <h3 class="text-2xl font-bold text-primary">89</h3>
      </div>
    </div>
  </main>
@endsection
