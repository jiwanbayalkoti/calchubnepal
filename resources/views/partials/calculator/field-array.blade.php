@php
    $name = $field['name'];
    $label = $field['label'];
    $itemSchema = $field['item_schema'] ?? [];
@endphp

{{--
    Repeatable-row builder for "array" input-schema fields (e.g. CGPA
    semesters, GPA courses). Rows are named field[__INDEX__][subfield] and
    cloned client-side from the <template> below — see the
    "Array field rows" section of public/js/calculator-hub.js.
--}}
<div class="col-12 js-array-field" data-field="{{ $name }}">
    <label class="form-label d-block">{{ $label }} <span class="text-danger">*</span></label>

    <div class="js-array-rows d-flex flex-column gap-2 mb-2"></div>

    <button type="button" class="btn btn-sm btn-outline-brand js-add-array-row" data-field="{{ $name }}">
        <i class="bi bi-plus-lg me-1"></i> Add row
    </button>
    <div class="invalid-feedback"></div>

    <template class="js-array-row-template">
        <div class="array-row row g-2 align-items-end border rounded-3 p-2 mb-1" style="border-color: var(--border) !important;">
            @foreach($itemSchema as $sub)
                <div class="col-md-{{ max(2, intdiv(10, max(count($itemSchema), 1))) }}">
                    <label class="form-label small mb-1">{{ $sub['label'] }}</label>
                    @if(($sub['type'] ?? 'text') === 'select')
                        <select name="{{ $name }}[__INDEX__][{{ $sub['name'] }}]" class="form-select form-select-sm">
                            @foreach(($sub['options'] ?? []) as $optKey => $optValue)
                                <option value="{{ is_int($optKey) ? $optValue : $optKey }}">{{ $optValue }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="{{ ($sub['type'] ?? 'text') === 'number' ? 'number' : 'text' }}"
                               name="{{ $name }}[__INDEX__][{{ $sub['name'] }}]"
                               class="form-control form-control-sm"
                               @if(isset($sub['min'])) min="{{ $sub['min'] }}" @endif
                               @if(isset($sub['max'])) max="{{ $sub['max'] }}" @endif
                               @if(isset($sub['step'])) step="{{ $sub['step'] }}" @endif
                               {{ ($sub['required'] ?? false) ? 'required' : '' }}>
                    @endif
                </div>
            @endforeach
            <div class="col-md-1">
                <button type="button" class="btn btn-sm btn-outline-danger js-remove-array-row" title="Remove row" aria-label="Remove row">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    </template>
</div>
