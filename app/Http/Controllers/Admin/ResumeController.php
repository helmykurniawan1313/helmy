<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PortfolioAssistant;
use App\Support\Resume;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class ResumeController extends Controller
{
    public function edit(): View
    {
        $file = fn ($path) => File::exists(public_path($path)) ? [
            'url' => asset($path).'?v='.File::lastModified(public_path($path)),
            'size' => round(File::size(public_path($path)) / 1024).' KB',
            'date' => date('d M Y, H:i', File::lastModified(public_path($path))),
        ] : null;

        return view('admin.resume', [
            'uploaded' => $file(Resume::UPLOADED),
            'generated' => $file(Resume::GENERATED),
            'active' => Settings::all()['resume_active'] ?? 'uploaded',
            'meta' => Settings::all()['resume_generated_meta'] ?? null,
            'hasAi' => collect(PortfolioAssistant::PROVIDERS)->contains(fn ($p) => (bool) config("services.$p.key")),
        ]);
    }

    public function upload(Request $request): RedirectResponse
    {
        $request->validate(['resume' => ['required', 'file', 'mimes:pdf', 'max:8192']], [], ['resume' => 'resume PDF']);

        File::ensureDirectoryExists(public_path(Resume::DIR));
        $request->file('resume')->move(public_path(Resume::DIR), basename(Resume::UPLOADED));
        Settings::put('resume_active', 'uploaded');

        return back()->with('status', 'Resume uploaded. Visitors now download this PDF.');
    }

    public function generate(Request $request, PortfolioAssistant $ai): RedirectResponse
    {
        $data = $request->validate([
            'mode' => ['required', 'in:ai,plain'],
            'locale' => ['required', 'in:en,id'],
            'publications' => ['nullable', 'boolean'],
        ]);

        @set_time_limit(180);
        $resume = Resume::data($data['locale'], (bool) ($data['publications'] ?? false));
        $provider = null;

        if ($data['mode'] === 'ai') {
            try {
                [$resume, $provider] = Resume::polish($resume, $data['locale'], $ai);
            } catch (Throwable $e) {
                return back()->withInput()->withErrors(['generate' => 'AI could not write the resume: '.Str::limit($e->getMessage(), 220)]);
            }
        }

        Resume::render($resume);
        Settings::put('resume_generated_meta', ($provider ? 'AI ('.ucfirst($provider).')' : 'Without AI').' · '.strtoupper($data['locale']));

        return back()->with('status', 'ATS resume generated'.($provider ? " with {$provider}" : '').'. Preview it below, then choose which file visitors download.');
    }

    public function activate(Request $request): RedirectResponse
    {
        $data = $request->validate(['active' => ['required', 'in:uploaded,generated']]);
        Settings::put('resume_active', $data['active']);

        return back()->with('status', 'Visitors now download the '.($data['active'] === 'generated' ? 'generated ATS' : 'uploaded').' resume.');
    }

    public function destroy(string $which): RedirectResponse
    {
        abort_unless(in_array($which, ['uploaded', 'generated'], true), 404);
        File::delete(public_path($which === 'uploaded' ? Resume::UPLOADED : Resume::GENERATED));

        return back()->with('status', ucfirst($which).' resume removed.');
    }
}
