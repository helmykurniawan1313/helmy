{{-- One repeatable item. $id is its form key ("__ID__" inside the template). --}}
@php $list = $section['list']; @endphp
<div class="repeat-item {{ $collapsed ? 'is-collapsed' : '' }}"
     data-title-field="{{ $list['title'] }}" data-subtitle-field="{{ $list['subtitle'] ?? '' }}">
    <div class="repeat-head" role="button" tabindex="0" aria-expanded="{{ $collapsed ? 'false' : 'true' }}">
        <i class="bi bi-chevron-right repeat-chevron" aria-hidden="true"></i>
        <span class="repeat-num"></span>
        <div class="repeat-text">
            <div class="repeat-title" data-empty="New {{ $list['item'] }}"></div>
            <div class="repeat-subtitle"></div>
        </div>
        <div class="repeat-actions">
            <button type="button" class="btn btn-icon" data-action="up" aria-label="Move up" title="Move up"><i class="bi bi-arrow-up"></i></button>
            <button type="button" class="btn btn-icon" data-action="down" aria-label="Move down" title="Move down"><i class="bi bi-arrow-down"></i></button>
            <button type="button" class="btn btn-icon btn-icon-danger" data-action="remove" aria-label="Remove" title="Remove"><i class="bi bi-trash3"></i></button>
        </div>
    </div>
    <div class="repeat-body">
        <div class="row">
            @foreach ($list['fields'] as $field)
                @include('admin.sections._field', [
                    'field' => $field,
                    'name' => "items[$id][{$field['key']}]",
                    'file' => "items[$id][{$field['key']}_file]",
                    'value' => $item[$field['key']] ?? ($field['type'] === 'select' ? array_key_first($field['options']) : null),
                    'id' => "f-$id-{$field['key']}",
                    'error' => "items.$id.{$field['key']}",
                ])
            @endforeach
        </div>
    </div>
</div>
