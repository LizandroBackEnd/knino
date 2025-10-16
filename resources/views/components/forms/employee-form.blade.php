@php
  $roles = $roles ?? ['administrador' => 'Administrador', 'recepcionista' => 'Recepcionista', 'veterinario' => 'Veterinario'];
  $fields = [
    ['name'=>'name','type'=>'text','label'=>'Nombre del Empleado','placeholder'=>'Ej: Ana Pérez','required'=>true],
    ['name'=>'email','type'=>'email','label'=>'Correo Electrónico','placeholder'=>'correo@ejemplo.com','required'=>true],
    ['name'=>'phone','type'=>'text','label'=>'Teléfono','placeholder'=>'555-0000'],
    ['name'=>'address','type'=>'text','label'=>'Dirección','placeholder'=>'Calle, número, colonia'],
  ];
@endphp

@include('components.forms.dynamic-form', ['fields' => $fields, 'gridCols' => 'grid-cols-1 md:grid-cols-2', 'rowGap' => 'gap-4'])

<div class="mt-2">
  <label class="block text-sm font-medium text-gray-700">Rol</label>
  <select name="role" class="mt-1 block w-full rounded-md border px-3 py-2 form-control">
    @foreach($roles as $value => $label)
      <option value="{{ $value }}" {{ old('role') == $value ? 'selected' : '' }}>{{ $label }}</option>
    @endforeach
  </select>
  @if($errors->has('role'))
    <p class="text-sm text-red-600 mt-1">{{ $errors->first('role') }}</p>
  @endif
</div>
