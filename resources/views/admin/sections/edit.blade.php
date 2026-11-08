@extends('admin.layouts.app')

@php
    $key = $section['key'];
    $routeArgs = $locale ? [$key, $locale] : [$key];
    $list = $section['list'] ?? null;
    // After a failed save, show what was typed instead of the stored data
    $rows = old('items', null) !== null ? old('items') : $items;
    $langLabel = $locale ? ' ('.strtoupper($locale).')' : '';
@endphp

@section('eyebrow', $section['group'])
@section('title', $section['title'])
@section('subtitle', $section['description'] ?? ($section['translatable'] ? 'Edited separately for each language.' : 'Shared by both languages.'))

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        @if ($section['translatable'])
            <div class="btn-group" role="group" aria-label="Language">
                @foreach (['en' => 'English', 'id' => 'Indonesia'] as $code => $label)
                    <a href="{{ route('admin.sections.edit', [$key, $code]) }}"
                       @class(['btn btn-sm', 'btn-primary' => $locale === $code, 'btn-outline-primary' => $locale !== $code])
                       @if ($locale === $code) aria-current="page" @endif>{{ strtoupper($code) }} <span class="d-none d-lg-inline">· {{ $label }}</span></a>
                @endforeach
            </div>
        @endif

        <a href="{{ route($locale === 'id' ? 'home.id' : 'home') }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-eye"></i> Preview
        </a>

        @if ($saved)
            <form method="POST" action="{{ route('admin.sections.reset', $routeArgs) }}"
                  data-confirm="Discard your saved version of {{ $section['title'] }}{{ $langLabel }} and go back to the default content?">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Go back to the default content">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </button>
            </form>
        @endif
    </div>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.sections.update', $routeArgs) }}" enctype="multipart/form-data" data-section-form novalidate class="section-form">
    @csrf @method('PUT')

    @if (! empty($section['fields']))
        <div class="card">
            @if ($list)
                <div class="card-header pb-0"><h5 class="card-title mb-0">Section text</h5></div>
            @endif
            <div class="card-body">
                <div class="row">
                    @foreach ($section['fields'] as $i => $field)
                        @include('admin.sections._field', [
                            'field' => $field,
                            'name' => "fields[$i]",
                            'file' => "files[$i]",
                            'value' => old("fields.$i", $values[$field['key']] ?? null),
                            'id' => "field-$i",
                            'error' => "fields.$i",
                        ])
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if ($list)
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center gap-2 pb-0">
                <h5 class="card-title mb-0 me-auto">
                    {{ Str::plural(Str::ucfirst($list['item'])) }}
                    <span class="badge bg-light-primary align-middle ms-1" data-repeat-count>{{ count($rows) }}</span>
                </h5>
                <div class="btn-group btn-group-sm" role="group" aria-label="Fold items">
                    <button type="button" class="btn btn-outline-secondary" data-collapse-all="true" title="Collapse all"><i class="bi bi-arrows-collapse"></i><span class="d-none d-md-inline ms-1">Collapse</span></button>
                    <button type="button" class="btn btn-outline-secondary" data-collapse-all="false" title="Expand all"><i class="bi bi-arrows-expand"></i><span class="d-none d-md-inline ms-1">Expand</span></button>
                </div>
                <button type="button" class="btn btn-sm btn-primary" data-repeat-add>
                    <i class="bi bi-plus-lg"></i> Add {{ $list['item'] }}
                </button>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3"><i class="bi bi-info-circle me-1"></i>The order here is the order on the website. Click an item to open or close it.</p>
                <div data-repeat-list>
                    @foreach ($rows as $rowKey => $item)
                        @include('admin.sections._item', ['id' => is_string($rowKey) ? $rowKey : 'i'.$rowKey, 'item' => $item, 'collapsed' => ! $errors->any()])
                    @endforeach
                </div>
                <div class="empty-state" data-repeat-empty @if (count($rows)) hidden @endif>
                    <i class="bi bi-inbox"></i>
                    <p class="mb-0">No {{ Str::plural($list['item']) }} yet. Use <strong>Add {{ $list['item'] }}</strong> to create one.</p>
                </div>
            </div>
        </div>

        <template id="repeat-template">
            @include('admin.sections._item', ['id' => '__ID__', 'item' => [], 'collapsed' => false])
        </template>
    @endif

    <div class="save-bar">
        <span class="save-state" data-save-state>
            <i class="bi bi-check-circle text-success"></i>
            <span>{{ $saved ? 'Saved '.$saved->updated_at->diffForHumans() : 'Showing default content' }}</span>
        </span>
        <span class="save-state is-dirty" data-dirty-state hidden>
            <i class="bi bi-circle-fill text-warning"></i> <span>Unsaved changes</span>
        </span>
        <div class="ms-auto d-flex gap-2">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save{{ $langLabel }}</button>
        </div>
    </div>
</form>
@endsection
