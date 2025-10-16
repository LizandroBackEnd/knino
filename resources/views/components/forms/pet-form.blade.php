@php
  $owners = $owners ?? (isset($ownersList) ? $ownersList : []);
  $fields = [
    ['name'=>'name','type'=>'text','label'=>'Nombre de la Mascota','placeholder'=>'Ej: Max','required'=>true],
    ['name'=>'species','type'=>'select','label'=>'Especie','options'=>[''=>'Seleccionar','dog'=>'Perro','cat'=>'Gato','other'=>'Otra'],'required'=>true],
    ['name'=>'breed','type'=>'text','label'=>'Raza','placeholder'=>'Ej: Labrador'],
  ['name'=>'age','type'=>'number','label'=>'Edad (años)','placeholder'=>'0','value'=>old('age',0)],
  ['name'=>'weight','type'=>'number','label'=>'Peso (kg)','placeholder'=>'0.0','value'=>old('weight',0),'step'=>'0.1'],
  ['name'=>'color','type'=>'text','label'=>'Color','placeholder'=>'Ej: Café'],
  ];
@endphp

{{-- Render the grid for the basic fields --}}
@include('components.forms.dynamic-form', ['fields' => $fields, 'gridCols' => 'grid-cols-1 md:grid-cols-2', 'rowGap' => 'gap-4'])

{{-- File input for photo and owner selector on separate row --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
  <div>
    <label class="block text-sm font-medium text-gray-700">Foto</label>
    <div class="mt-1">
      <label class="inline-flex items-center px-3 py-2 bg-white border rounded-md cursor-pointer text-sm text-gray-700 hover:bg-gray-50 form-control">
        Seleccionar archivo
        <input type="file" name="photo" accept="image/*" class="sr-only js-file-input">
      </label>
      {{-- If you want to show filename, uncomment the span below or let JS update it. --}}
      <span class="text-sm text-gray-500 ml-3 js-file-name" aria-hidden="true"></span>
    </div>
    @if($errors->has('photo'))
      <p class="text-sm text-red-600 mt-1">{{ $errors->first('photo') }}</p>
    @endif
  </div>

  <div>
    <label class="block text-sm font-medium text-gray-700">Dueño</label>
    <select name="owner_id" class="mt-1 block w-full rounded-md border px-3 py-2">
      <option value="">Seleccionar</option>
      @foreach($owners as $id => $label)
        <option value="{{ $id }}" {{ old('owner_id') == $id ? 'selected' : '' }}>{{ $label }}</option>
      @endforeach
    </select>
    @if($errors->has('owner_id'))
      <p class="text-sm text-red-600 mt-1">{{ $errors->first('owner_id') }}</p>
    @endif
  </div>
</div>
