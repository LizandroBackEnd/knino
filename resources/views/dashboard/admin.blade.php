@extends('layouts.dashboard')

@section('content')
  <main class="p-6" style="font-family: var(--font-secondary);">
    <h2 class="text-lg font-bold mb-4" style="font-family: var(--font-primary);">
      Bienvenido al sistema K-NINO
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white p-4 rounded shadow hover:shadow-lg transition-shadow duration-150 flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-600">Servicios registrados</p>
          <h3 class="text-3xl font-bold text-primary">{{ $servicesCount ?? 0 }}</h3>
        </div>
        <div class="flex items-center justify-center w-12 h-12 bg-[var(--color-primary)] rounded-full ml-4">
          <!-- services icon -->
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7M7 7V5a2 2 0 012-2h6a2 2 0 012 2v2"/></svg>
        </div>
      </div>

      <div class="bg-white p-4 rounded shadow hover:shadow-lg transition-shadow duration-150 flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-600">Clientes</p>
          <h3 class="text-3xl font-bold text-primary">{{ $clientsCount ?? 0 }}</h3>
        </div>
        <div class="flex items-center justify-center w-12 h-12 bg-green-500 rounded-full ml-4">
          <!-- clients icon -->
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5V8a2 2 0 00-2-2h-3M2 20h5V8a2 2 0 00-2-2H2m13 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </div>
      </div>

      <div class="bg-white p-4 rounded shadow hover:shadow-lg transition-shadow duration-150 flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-600">Mascotas</p>
          <h3 class="text-3xl font-bold text-primary">{{ $petsCount ?? 0 }}</h3>
        </div>
        <div class="flex items-center justify-center w-12 h-12 bg-pink-500 rounded-full ml-4">
          <!-- pets icon -->
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21c0-4 3-7 5-7s5 3 5 7M4 10a2 2 0 11-4 0 2 2 0 014 0zm8-6a2 2 0 11-4 0 2 2 0 014 0zm8 6a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
      </div>
    </div>
  </main>
@endsection
