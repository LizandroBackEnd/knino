@php
  // $fields should be an array of field definitions:
  // ['name'=>'email','type'=>'email','label'=>'Correo','placeholder'=>'...','required'=>true,'value'=>old('email')]
  $fields = $fields ?? [];
  $gridCols = $gridCols ?? 'grid-cols-1';
  $rowGap = $rowGap ?? 'gap-4';
@endphp

<div class="grid {{ $gridCols }} {{ $rowGap }}">
  @foreach($fields as $f)
    <div class="">
      <label class="block text-sm font-medium text-gray-700">{{ $f['label'] ?? ucfirst($f['name']) }} @if(!empty($f['required'])) * @endif</label>
      @php $value = old($f['name'], $f['value'] ?? '') @endphp
      @if($f['type'] === 'textarea')
        <textarea name="{{ $f['name'] }}" placeholder="{{ $f['placeholder'] ?? '' }}" class="form-control mt-1 block w-full">{{ $value }}</textarea>
      @elseif($f['type'] === 'select')
        <select name="{{ $f['name'] }}" class="form-control mt-1 block w-full">
          @foreach($f['options'] ?? [] as $optValue => $optLabel)
            <option value="{{ $optValue }}" {{ $optValue == $value ? 'selected' : '' }}>{{ $optLabel }}</option>
          @endforeach
        </select>
      @else
        <input type="{{ $f['type'] ?? 'text' }}" name="{{ $f['name'] }}" value="{{ $value }}" placeholder="{{ $f['placeholder'] ?? '' }}" class="form-control mt-1 block w-full" @if(!empty($f['required'])) required @endif>
      @endif
      @if($errors->has($f['name']))
        <p class="text-sm text-red-600 mt-1">{{ $errors->first($f['name']) }}</p>
      @endif
    </div>
  @endforeach
</div>
