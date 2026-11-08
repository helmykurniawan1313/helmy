@extends('admin.layouts.app')

@section('eyebrow', 'Settings')
@section('title', 'AI keys & models')
@section('subtitle', 'Connect the "Ask AI" chat on your website. Turn on one or more providers and set their order.')

@section('content')
<form method="POST" action="{{ route('admin.settings.ai.update') }}" autocomplete="off" data-section-form class="section-form">
    @csrf @method('PUT')

    <div class="row g-3">
        @foreach ($providers as $id => $prov)
            @php $priority = (int) old("priority.$id", $prov['priority']); @endphp
            <div class="col-12 col-xl-4">
                <div @class(['card provider-card h-100 mb-0', 'is-off' => $priority === 0])>
                    <div class="card-header d-flex align-items-start gap-2">
                        <div class="me-auto">
                            <h5 class="card-title mb-1">{{ $prov['name'] }}</h5>
                            <div class="text-muted small">{{ $prov['note'] }}</div>
                        </div>
                        @if ($prov['source'] === 'admin')
                            <span class="badge bg-light-success flex-none"><i class="bi bi-check-circle"></i> Key saved</span>
                        @elseif ($prov['source'] === 'env')
                            <span class="badge bg-light-info flex-none"><i class="bi bi-file-earmark-code"></i> .env key</span>
                        @else
                            <span class="badge bg-light-secondary flex-none">No key</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="priority_{{ $id }}" class="form-label">Use</label>
                            <select id="priority_{{ $id }}" name="priority[{{ $id }}]" class="form-select" data-priority>
                                <option value="1" @selected($priority === 1)>1st: ask first</option>
                                <option value="2" @selected($priority === 2)>2nd: first fallback</option>
                                <option value="3" @selected($priority === 3)>3rd: last fallback</option>
                                <option value="0" @selected($priority === 0)>Off</option>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="{{ $id }}_key" class="form-label">API key</label>
                            <input type="password" id="{{ $id }}_key" name="{{ $id }}_key" class="form-control @error($id.'_key') is-invalid @enderror"
                                   placeholder="{{ $prov['mask'] ? 'Current: '.$prov['mask'] : $prov['placeholder'] }}" autocomplete="new-password" spellcheck="false">
                            @error($id.'_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="form-text text-muted">
                                {{ $prov['mask'] ? 'Leave empty to keep the current key.' : 'Paste your key.' }}
                                <a href="{{ $prov['console'] }}" target="_blank" rel="noopener">Get a key <i class="bi bi-box-arrow-up-right"></i></a>
                            </small>
                            @if ($prov['source'] === 'admin')
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="clear[]" value="{{ $id }}_key" id="clear_{{ $id }}">
                                    <label class="form-check-label small" for="clear_{{ $id }}">Remove saved key</label>
                                </div>
                            @endif
                        </div>

                        <div class="form-group mb-0">
                            <label for="{{ $id }}_model" class="form-label">Model</label>
                            <input type="text" id="{{ $id }}_model" name="{{ $id }}_model" value="{{ old($id.'_model', $prov['model']) }}"
                                   class="form-control @error($id.'_model') is-invalid @enderror" placeholder="{{ $prov['default_model'] }}" spellcheck="false">
                            @error($id.'_model')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="form-text text-muted">Empty = <code>{{ $prov['default_model'] }}</code>.</small>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end">
                        <button type="submit" form="test-{{ $id }}" class="btn btn-sm btn-outline-primary" @disabled(! $prov['source'])
                                title="{{ $prov['source'] ? 'Send a tiny test request' : 'Save a key first' }}">
                            <i class="bi bi-lightning-charge"></i> Test
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="alert alert-light-secondary mt-4 mb-0">
        <i class="bi bi-info-circle me-1"></i>
        The chat asks the <strong>1st</strong> provider; if it fails or is slow, it moves to the <strong>2nd</strong>, then the <strong>3rd</strong>.
        Keys are encrypted before they're stored and never reach the browser. Save before testing.
    </div>

    <div class="save-bar">
        <span class="save-state" data-save-state><i class="bi bi-shield-lock text-muted"></i> <span>Keys are stored encrypted</span></span>
        <span class="save-state is-dirty" data-dirty-state hidden><i class="bi bi-circle-fill text-warning"></i> <span>Unsaved changes</span></span>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary ms-auto">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save AI settings</button>
    </div>
</form>

{{-- Test buttons live outside the main form so they don't submit it --}}
@foreach ($providers as $id => $prov)
    <form id="test-{{ $id }}" method="POST" action="{{ route('admin.settings.ai.test', $id) }}" hidden>@csrf</form>
@endforeach
@endsection
