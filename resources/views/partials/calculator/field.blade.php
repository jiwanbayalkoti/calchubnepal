@php
    $name = $field['name'];
    $label = $field['label'];
    $type = $field['type'] ?? 'number';
    $unit = $field['unit'] ?? null;
    $required = $field['required'] ?? true;
    $default = $field['default'] ?? old($name);
@endphp

@if($type === 'array')
    @include('partials.calculator.field-array', ['field' => $field])
@else
<div class="col-md-6">
    <label for="field-{{ $name }}" class="form-label">
        {{ $label }} @if($unit)<span class="text-muted-custom">({{ $unit }})</span>@endif
        @if($required)<span class="text-danger">*</span>@endif
    </label>

    @if($type === 'select' || $type === 'radio')
        <select name="{{ $name }}" id="field-{{ $name }}" class="form-select js-select2" {{ $required ? 'required' : '' }}>
            @foreach(($field['options'] ?? []) as $value => $optionLabel)
                <option value="{{ $value }}" {{ (string) $default === (string) $value ? 'selected' : '' }}>{{ $optionLabel }}</option>
            @endforeach
        </select>

    @elseif($type === 'boolean')
        <div class="form-check form-switch mt-2">
            <input type="checkbox" class="form-check-input" role="switch" id="field-{{ $name }}" name="{{ $name }}" value="1" {{ $default ? 'checked' : '' }}>
        </div>

    @elseif($type === 'date')
        <input type="date" name="{{ $name }}" id="field-{{ $name }}" class="form-control" value="{{ $default }}" {{ $required ? 'required' : '' }}>

    @elseif($type === 'time')
        <input type="time" name="{{ $name }}" id="field-{{ $name }}" class="form-control" value="{{ $default }}" {{ $required ? 'required' : '' }}>

    @elseif($type === 'integer')
        <input type="number" step="1" name="{{ $name }}" id="field-{{ $name }}" class="form-control"
               value="{{ $default }}"
               @if(isset($field['min'])) min="{{ $field['min'] }}" @endif
               @if(isset($field['max'])) max="{{ $field['max'] }}" @endif
               {{ $required ? 'required' : '' }}>

    @elseif($type === 'number')
        <input type="number" step="{{ $field['step'] ?? 'any' }}" name="{{ $name }}" id="field-{{ $name }}" class="form-control"
               value="{{ $default }}"
               @if(isset($field['min'])) min="{{ $field['min'] }}" @endif
               @if(isset($field['max'])) max="{{ $field['max'] }}" @endif
               {{ $required ? 'required' : '' }}>

    @else
        <input type="text" name="{{ $name }}" id="field-{{ $name }}" class="form-control"
               value="{{ $default }}"
               @if(isset($field['max_length'])) maxlength="{{ $field['max_length'] }}" @endif
               {{ $required ? 'required' : '' }}>
    @endif

    <div class="invalid-feedback"></div>
</div>
@endif
