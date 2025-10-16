@props(['id' => 'modalForm', 'title' => 'Formulario'])
<div id="{{ $id }}" class="modal-root fixed inset-0 z-50 grid place-items-center opacity-0 pointer-events-none transition-opacity duration-150 js-modal-hidden" style="display: none;">
  <div class="modal-overlay absolute inset-0 bg-black/40 transition-opacity duration-150"></div>

  <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 p-6" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title">
    <button class="absolute top-3 right-3 js-close-modal text-gray-500 hover:text-gray-700" aria-label="Cerrar">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>

    <h3 id="{{ $id }}-title" class="text-lg font-bold mb-1">{{ $title }}</h3>
    <p class="text-sm text-gray-600 mb-4">{{ $slotTitle ?? '' }}</p>

    <div>
      {{ $slot }}
    </div>
  </div>
</div>
