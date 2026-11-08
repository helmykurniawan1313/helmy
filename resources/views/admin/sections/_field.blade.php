{{--
    One form control.
    $field  definition from App\Admin\Sections
    $name   input name, e.g. "fields[0]" or "items[abc][title]"
    $file   name for the upload input (image fields only)
    $value  stored value (arrays are joined for textareas)
    $id     unique DOM id
    $error  validation key for this field
--}}
@php
    $type = $field['type'];
    $display = match ($type) {
        'lines' => is_array($value) ? implode("\n", $value) : $value,
        'paragraphs' => is_array($value) ? implode("\n\n", $value) : $value,
        'tags' => is_array($value) ? implode(', ', $value) : $value,
        default => is_array($value) ? '' : $value,
    };
    $invalid = $errors->has($error);
    $rows = match ($type) { 'paragraphs' => 7, 'lines' => 5, default => 3 };
@endphp
<div class="col-12 col-md-{{ $field['col'] ?? 12 }}">
    <div class="form-group mb-3">
        @if ($type === 'bool')
            <div class="form-check form-switch mt-md-4 pt-md-2">
                <input type="hidden" name="{{ $name }}" value="0">
                <input class="form-check-input" type="checkbox" role="switch" id="{{ $id }}" name="{{ $name }}" value="1" @checked($display)>
                <label class="form-check-label fw-bold" for="{{ $id }}">{{ $field['label'] }}</label>
            </div>
        @else
            <label for="{{ $id }}" class="form-label fw-bold">
                {{ $field['label'] }}@if ($field['required'] ?? false) <span class="text-danger" aria-hidden="true">*</span>@endif
            </label>

            @if (in_array($type, ['textarea', 'lines', 'paragraphs'], true))
                <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" data-field="{{ $field['key'] }}"
                          @class(['form-control', 'is-invalid' => $invalid]) @required($field['required'] ?? false)>{{ $display }}</textarea>
            @elseif ($type === 'select')
                <select id="{{ $id }}" name="{{ $name }}" data-field="{{ $field['key'] }}" @class(['form-select', 'is-invalid' => $invalid])>
                    @foreach ($field['options'] as $optValue => $optLabel)
                        <option value="{{ $optValue }}" @selected((string) $display === (string) $optValue)>{{ $optLabel }}</option>
                    @endforeach
                </select>
            @elseif ($type === 'image')
                <div class="image-field">
                    <input type="hidden" name="{{ $name }}" value="{{ $display }}">
                    <img src="{{ $display ? asset($display) : '' }}" alt="" class="d-block mb-2" @if (! $display) hidden @endif>
                    <div class="input-group">
                        <input type="file" id="{{ $id }}" name="{{ $file }}" accept="image/jpeg,image/png,image/webp" data-preview
                               @class(['form-control', 'is-invalid' => $errors->has(str_replace(['[', ']'], ['.', ''], $file))])>
                        <button type="button" class="btn btn-outline-secondary" data-image-clear>Remove</button>
                    </div>
                </div>
            @else
                <input type="{{ $type === 'number' ? 'number' : ($type === 'tags' ? 'text' : $type) }}" id="{{ $id }}" name="{{ $name }}"
                       value="{{ $display }}" data-field="{{ $field['key'] }}"
                       @class(['form-control', 'is-invalid' => $invalid]) @required($field['required'] ?? false)>
            @endif
        @endif

        @if ($invalid)
            <div class="invalid-feedback d-block">{{ $errors->first($error) }}</div>
        @elseif (! empty($field['help']))
            <small class="form-text text-muted">{{ $field['help'] }}</small>
        @endif
    </div>
</div>
