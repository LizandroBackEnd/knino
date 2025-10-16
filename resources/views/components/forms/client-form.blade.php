@php
  $fields = [
    ['name'=>'name','type'=>'text','label'=>'Nombre Completo','placeholder'=>'Ej: Juan Pérez','required'=>true],
    ['name'=>'email','type'=>'email','label'=>'Correo Electrónico','placeholder'=>'correo@ejemplo.com','required'=>true],
    ['name'=>'phone','type'=>'text','label'=>'Teléfono','placeholder'=>'555-0000','required'=>true],
    ['name'=>'address','type'=>'text','label'=>'Dirección','placeholder'=>'Calle, número, colonia']
  ];
@endphp

@include('components.forms.dynamic-form', ['fields' => $fields, 'gridCols' => 'grid-cols-1', 'rowGap' => 'gap-4'])
