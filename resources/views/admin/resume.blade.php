@extends('admin.layouts.app')

@section('eyebrow', 'Settings')
@section('title', 'Resume (PDF)')
@section('subtitle', 'Upload your own resume, or generate an ATS-friendly one from this website. Visitors get a "Download CV" button.')

@section('content')
@error('generate')<div class="alert alert-light-danger"><i class="bi bi-exclamation-triangle me-1"></i> {{ $message }}</div>@enderror

<div class="row g-3">
    {{-- Upload your own --}}
    <div class="col-12 col-xl-6">
        <div class="card h-100 mb-0">
            <div class="card-header">
                <h5 class="card-title mb-1">Your own PDF</h5>
                <div class="text-muted small">The resume you already have, e.g. exported from Word or Canva.</div>
            </div>
            <div class="card-body">
                @if ($uploaded)
                    <div class="d-flex align-items-center gap-3 p-3 mb-3 rounded-3 border">
                        <i class="bi bi-file-earmark-pdf-fill fs-2 text-danger"></i>
                        <div class="me-auto">
                            <div class="fw-semibold">Uploaded resume</div>
                            <div class="text-muted small">{{ $uploaded['size'] }} · {{ $uploaded['date'] }}</div>
                        </div>
                        <a href="{{ $uploaded['url'] }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> View</a>
                        <form method="POST" action="{{ route('admin.resume.destroy', 'uploaded') }}" data-confirm="Remove the uploaded resume?">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Remove"><i class="bi bi-trash3"></i></button>
                        </form>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.resume.upload') }}" enctype="multipart/form-data">
                    @csrf
                    <label for="resume" class="form-label">{{ $uploaded ? 'Replace with a new PDF' : 'Upload PDF' }}</label>
                    <input type="file" id="resume" name="resume" accept="application/pdf" class="form-control @error('resume') is-invalid @enderror" required>
                    @error('resume')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="form-text text-muted">PDF only, up to 8 MB.</small>
                    <div class="mt-3"><button class="btn btn-primary"><i class="bi bi-upload"></i> Upload</button></div>
                </form>
            </div>
        </div>
    </div>

    {{-- Generate from the website --}}
    <div class="col-12 col-xl-6">
        <div class="card h-100 mb-0">
            <div class="card-header">
                <h5 class="card-title mb-1">Generate ATS resume</h5>
                <div class="text-muted small">Built from your website content: one column, standard headings, plain text that job portals can read.</div>
            </div>
            <div class="card-body">
                @if ($generated)
                    <div class="d-flex align-items-center gap-3 p-3 mb-3 rounded-3 border">
                        <i class="bi bi-file-earmark-text-fill fs-2 text-primary"></i>
                        <div class="me-auto">
                            <div class="fw-semibold">Generated resume</div>
                            <div class="text-muted small">{{ $meta ? $meta.' · ' : '' }}{{ $generated['size'] }} · {{ $generated['date'] }}</div>
                        </div>
                        <a href="{{ $generated['url'] }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> View</a>
                        <form method="POST" action="{{ route('admin.resume.destroy', 'generated') }}" data-confirm="Remove the generated resume?">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Remove"><i class="bi bi-trash3"></i></button>
                        </form>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.resume.generate') }}" data-generate-form>
                    @csrf
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="mode">Writing</label>
                            <select id="mode" name="mode" class="form-select">
                                <option value="ai" @selected(old('mode', $hasAi ? 'ai' : 'plain') === 'ai') @disabled(! $hasAi)>With AI (rewrites for ATS)</option>
                                <option value="plain" @selected(old('mode', $hasAi ? 'ai' : 'plain') === 'plain')>Without AI (exact website text)</option>
                            </select>
                            @unless ($hasAi)<small class="form-text text-muted">Add an AI key in <a href="{{ route('admin.settings.ai') }}">AI keys</a> to enable.</small>@endunless
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="locale">Language</label>
                            <select id="locale" name="locale" class="form-select">
                                <option value="en" @selected(old('locale', 'en') === 'en')>English</option>
                                <option value="id" @selected(old('locale') === 'id')>Bahasa Indonesia</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="hidden" name="publications" value="0">
                                <input class="form-check-input" type="checkbox" id="publications" name="publications" value="1" @checked(old('publications'))>
                                <label class="form-check-label" for="publications">Include publications</label>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-light-secondary small mt-3 mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        AI only rewrites wording (summary, keywords, bullet points). It is told not to invent jobs, dates or numbers, but always read the PDF before using it.
                    </div>
                    <button class="btn btn-primary" data-generate-btn><i class="bi bi-stars"></i> {{ $generated ? 'Generate again' : 'Generate PDF' }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Which one visitors download --}}
<div class="card mt-4 mb-0">
    <div class="card-body d-flex flex-wrap align-items-center gap-3">
        <div class="me-auto">
            <h5 class="card-title mb-1">Visitors download</h5>
            <div class="text-muted small">
                @if ($uploaded || $generated)
                    Shown as a "Download CV" button on the website. <a href="{{ route('resume') }}" target="_blank" rel="noopener">Test the download</a>
                @else
                    Nothing yet. Upload or generate a resume to show the button.
                @endif
            </div>
        </div>
        <form method="POST" action="{{ route('admin.resume.activate') }}" class="d-flex gap-2 align-items-center">
            @csrf @method('PUT')
            <select name="active" class="form-select" @disabled(! ($uploaded && $generated))>
                <option value="uploaded" @selected($active === 'uploaded') @disabled(! $uploaded)>Uploaded PDF</option>
                <option value="generated" @selected($active === 'generated') @disabled(! $generated)>Generated ATS PDF</option>
            </select>
            <button class="btn btn-outline-primary" @disabled(! ($uploaded && $generated))>Save</button>
        </form>
    </div>
</div>

<script>
    // Generating with AI takes a while: show progress and stop double submits
    document.querySelector('[data-generate-form]')?.addEventListener('submit', (e) => {
        const btn = e.target.querySelector('[data-generate-btn]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Generating… (up to a minute with AI)';
    });
</script>
@endsection
