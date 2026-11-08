@extends('admin.layouts.app')

@section('title', 'Welcome back, '.Str::before(auth()->user()->name, ' '))
@section('subtitle', 'Everything on your portfolio, section by section.')
@section('actions')
    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="btn btn-primary"><i class="bi bi-eye me-1"></i> Preview site</a>
@endsection

@section('content')
<div class="row g-3 mb-4">
    @foreach ($stats as $stat)
        <div class="col-6 col-xl-3">
            <a href="{{ route('admin.sections.edit', $stat['section']) }}" class="card stat-card mb-0 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stats-icon {{ $stat['color'] }} flex-none"><i class="bi bi-{{ $stat['icon'] }}"></i></div>
                    <div>
                        <div class="stat-value">{{ $stat['value'] }}</div>
                        <div class="text-muted small">{{ $stat['label'] }}</div>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

<div class="row g-4">
    <div class="col-12 col-xxl-8">
        @foreach ($groups as $group => $sections)
            <h6 class="group-label"><i class="bi bi-{{ App\Admin\Sections::GROUP_ICONS[$group] ?? 'folder' }} me-1"></i>{{ $group }}</h6>
            <div class="row g-3 mb-4">
                @foreach ($sections as $key => $s)
                    @php
                        $rows = $edited[$key] ?? collect();
                        $editedLocales = $rows->pluck('locale')->all();
                    @endphp
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card section-card mb-0 h-100">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex align-items-start gap-3 mb-2">
                                    <span class="section-icon"><i class="bi bi-{{ $s['icon'] }}"></i></span>
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.sections.edit', $key) }}" class="section-title stretched-link-off">{{ $s['title'] }}</a>
                                        <div class="text-muted small">{{ $s['description'] ?? ($s['translatable'] ? 'English & Indonesian' : 'Shared by both languages') }}</div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2 mt-auto pt-2">
                                    @if ($s['translatable'])
                                        @foreach (['en', 'id'] as $loc)
                                            <a href="{{ route('admin.sections.edit', [$key, $loc]) }}"
                                               class="btn btn-sm {{ in_array($loc, $editedLocales, true) ? 'btn-light-success' : 'btn-outline-secondary' }}"
                                               title="{{ in_array($loc, $editedLocales, true) ? 'Edited' : 'Default content' }}">
                                                {{ strtoupper($loc) }}@if (in_array($loc, $editedLocales, true)) <i class="bi bi-check2"></i>@endif
                                            </a>
                                        @endforeach
                                    @else
                                        <a href="{{ route('admin.sections.edit', $key) }}"
                                           class="btn btn-sm {{ $rows->isNotEmpty() ? 'btn-light-success' : 'btn-outline-secondary' }}">
                                            Edit @if ($rows->isNotEmpty())<i class="bi bi-check2"></i>@endif
                                        </a>
                                    @endif
                                    <span class="ms-auto small text-muted">
                                        {{ $rows->isNotEmpty() ? 'Saved '.$rows->max('updated_at')->diffForHumans(short: true) : 'Default' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

    <div class="col-12 col-xxl-4">
        <div class="row g-3">
            <div class="col-12 col-md-6 col-xxl-12">
                <div class="card mb-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-xl bg-primary">
                                <span class="avatar-content fs-5">{{ collect(explode(' ', $p['name']))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('') }}</span>
                            </div>
                            <div>
                                <h5 class="mb-0">{{ $p['name'] }}</h5>
                                <div class="text-muted">{{ $p['role'] }}</div>
                            </div>
                        </div>
                        <hr>
                        <h6 class="mb-2">Last change</h6>
                        @if ($lastEdit)
                            @php $s = App\Admin\Sections::find($lastEdit->section); @endphp
                            <p class="mb-0">
                                <a href="{{ route('admin.sections.edit', $lastEdit->locale === 'all' ? [$lastEdit->section] : [$lastEdit->section, $lastEdit->locale]) }}">
                                    {{ $s['title'] ?? $lastEdit->section }}{{ $lastEdit->locale !== 'all' ? ' ('.strtoupper($lastEdit->locale).')' : '' }}
                                </a>
                                <span class="text-muted small">· {{ $lastEdit->updated_at->diffForHumans() }}</span>
                            </p>
                        @else
                            <p class="text-muted small mb-0">Nothing edited yet. The site shows the default content.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xxl-12">
                <div class="card mb-0 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <h6 class="mb-0 me-auto"><i class="bi bi-robot me-1"></i> AI assistant</h6>
                            <a href="{{ route('admin.settings.ai') }}" class="small">Manage</a>
                        </div>
                        @php $order = array_map('trim', explode(',', (string) $assistant['order'])); @endphp
                        @foreach ($order as $i => $provider)
                            @continue(! isset($assistant[$provider]))
                            <div class="d-flex align-items-center justify-content-between py-2 {{ $loop->last ? '' : 'border-bottom' }}">
                                <div>
                                    <span class="fw-bold text-capitalize">{{ $provider }}</span>
                                    <span class="text-muted small ms-1">{{ $i === 0 ? 'first' : 'fallback' }}</span>
                                </div>
                                @if ($assistant[$provider])
                                    <span class="badge bg-light-success"><i class="bi bi-check-circle"></i> Key set</span>
                                @else
                                    <span class="badge bg-light-danger"><i class="bi bi-x-circle"></i> No key</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
