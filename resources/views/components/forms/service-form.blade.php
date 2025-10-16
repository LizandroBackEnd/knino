@php
  $fields = [
    ['name'=>'name','type'=>'text','label'=>'Nombre del Servicio','placeholder'=>'Ej: Corte de Pelo','required'=>true],
    ['name'=>'description','type'=>'textarea','label'=>'Descripción','placeholder'=>'Breve descripción del servicio'],
    ['name'=>'cost','type'=>'number','label'=>'Costo','placeholder'=>'0.00','value'=>old('cost',0),'step'=>'0.01'],
  ];
@endphp

@include('components.forms.dynamic-form', ['fields' => $fields, 'gridCols' => 'grid-cols-1', 'rowGap' => 'gap-4'])

{{-- Foto del servicio --}}
<div class="mt-2">
  <label class="block text-sm font-medium text-gray-700">Foto</label>
  <div class="mt-1">
    <label class="inline-flex items-center px-3 py-2 bg-white border rounded-md cursor-pointer text-sm text-gray-700 hover:bg-gray-50 form-control">
      Seleccionar archivo
      <input type="file" name="photo" accept="image/*" class="sr-only js-file-input">
    </label>
    <span class="text-sm text-gray-500 ml-3 js-file-name" aria-hidden="true"></span>
  </div>
  @if($errors->has('photo'))
    <p class="text-sm text-red-600 mt-1">{{ $errors->first('photo') }}</p>
  @endif
</div>
