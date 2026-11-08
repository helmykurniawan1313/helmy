<?php

namespace App\Support;

use App\Services\PortfolioAssistant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;

/**
 * The downloadable resume: either a PDF uploaded in the admin, or an ATS-friendly
 * PDF generated from the website's own content (optionally polished by AI).
 */
class Resume
{
    public const DIR = 'uploads/resume';

    public const UPLOADED = 'uploads/resume/resume-uploaded.pdf';

    public const GENERATED = 'uploads/resume/resume-ats.pdf';

    /** Which file visitors get: "uploaded" or "generated" (falls back to whichever exists). */
    public static function activePath(): ?string
    {
        $choice = Settings::all()['resume_active'] ?? 'uploaded';
        $order = $choice === 'generated' ? [self::GENERATED, self::UPLOADED] : [self::UPLOADED, self::GENERATED];

        foreach ($order as $path) {
            if (File::exists(public_path($path))) {
                return $path;
            }
        }

        return null;
    }

    public static function available(): bool
    {
        return self::activePath() !== null;
    }

    public static function downloadName(): string
    {
        return Str::slug(config('portfolio.name', 'resume')).'-resume.pdf';
    }

    /** Plain resume data straight from the site (no AI). */
    public static function data(string $locale, bool $withPublications = false): array
    {
        $p = PortfolioContent::for($locale);
        $levels = $p['levels'] ?? [];

        $experience = collect($p['experiences'])->map(fn ($r) => [
            'title' => $r['title'],
            'org' => $r['org'],
            'type' => $r['type'] ?? null,
            'period' => $r['period'],
            'points' => array_values($r['points'] ?? []),
            'tags' => array_values((array) ($r['tags'] ?? [])),
        ])->values()->all();

        $skills = [];
        foreach ($p['skill_groups'] ?? [] as $key => $name) {
            $items = collect($p['skills'])->where('group', $key)
                ->map(fn ($s) => $s['name'].(isset($levels[$s['level']]) ? ' ('.$levels[$s['level']].')' : ''))
                ->values()->all();
            if ($items) {
                $skills[] = ['group' => $name, 'items' => $items];
            }
        }

        $about = $p['banner']['about_section'] ?? [];

        return [
            'name' => $p['name'],
            'headline' => $p['role'],
            'contact' => array_filter([
                $p['location'] ?? null,
                $p['phone'] ?? null,
                $p['email'] ?? null,
                isset($p['website']) ? preg_replace('#^https?://#', '', $p['website']) : null,
            ]),
            'summary' => trim(($about['lead'] ?? '').' '.implode(' ', $about['body'] ?? [])),
            'core_skills' => [],
            'experience' => $experience,
            'skills' => $skills,
            'education' => collect($p['education'])->map(fn ($e) => [
                'degree' => $e['degree'], 'school' => $e['school'], 'period' => $e['period'], 'points' => array_values($e['points'] ?? []),
            ])->all(),
            'certificates' => collect($p['certificates'])->map(fn ($c) => [
                'title' => $c['title'], 'issuer' => $c['issuer'], 'date' => $c['date'] ?? null, 'id' => $c['credential_id'] ?? null,
            ])->all(),
            'badges' => collect($p['badges'] ?? [])->map(fn ($c) => [
                'title' => $c['title'], 'issuer' => $c['issuer'], 'date' => $c['date'] ?? null, 'id' => $c['credential_id'] ?? null,
            ])->all(),
            'publications' => $withPublications ? collect($p['publications'] ?? [])->map(fn ($x) => [
                'title' => $x['title'], 'venue' => $x['venue'] ?? null, 'year' => $x['year'] ?? null, 'authors' => $x['authors'] ?? null,
            ])->all() : [],
            'labels' => self::labels($locale),
        ];
    }

    /**
     * Ask the AI to rewrite summary + bullets for ATS (keywords, action verbs),
     * strictly from the given facts. Returns [data, provider].
     */
    public static function polish(array $data, string $locale, PortfolioAssistant $ai): array
    {
        $language = $locale === 'id' ? 'Indonesian (Bahasa Indonesia)' : 'English';

        $facts = [
            'headline' => $data['headline'],
            'summary' => $data['summary'],
            'experience' => array_map(fn ($r) => array_intersect_key($r, array_flip(['title', 'org', 'period', 'points', 'tags'])), $data['experience']),
            'skills' => $data['skills'],
            'certificates' => array_column($data['certificates'], 'title'),
        ];

        $system = 'You are an expert resume writer who optimises resumes for Applicant Tracking Systems (ATS). '
            .'You only use facts that are given to you. You never invent employers, dates, numbers, metrics, tools, degrees or achievements.';

        $prompt = "Rewrite this candidate's resume content in {$language} so it is ATS-friendly.\n\n"
            ."Rules:\n"
            ."- summary: 3 to 4 sentences, professional, keyword-rich, first person implied (no \"I\").\n"
            ."- core_skills: 10 to 16 short keyword phrases taken from the experience, tags and skills (e.g. \"Network Administration\", \"Laravel\").\n"
            ."- experience: return EXACTLY ".count($data['experience'])." items in the SAME order. For each, 2 to 5 concise bullet points that start with a strong action verb. Keep every fact; merge or tighten wording only. No numbers that are not in the input.\n"
            ."- Plain text only. No markdown, no emojis.\n\n"
            ."Return ONLY valid JSON with this shape:\n"
            .'{"summary": "...", "core_skills": ["..."], "experience": [{"points": ["..."]}]}'
            ."\n\nCandidate facts (JSON):\n".json_encode($facts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        $result = $ai->complete($system, $prompt, 6000, 90);
        $json = self::extractJson($result['text']);

        if (! is_array($json)) {
            throw new \RuntimeException('The AI reply was not valid JSON. Try again or use "Without AI".');
        }

        if (! empty($json['summary']) && is_string($json['summary'])) {
            $data['summary'] = trim($json['summary']);
        }
        if (! empty($json['core_skills']) && is_array($json['core_skills'])) {
            $data['core_skills'] = array_values(array_filter(array_map(fn ($s) => is_string($s) ? trim($s) : null, $json['core_skills'])));
        }
        foreach ($json['experience'] ?? [] as $i => $role) {
            $points = array_values(array_filter(array_map(fn ($s) => is_string($s) ? trim($s) : null, (array) ($role['points'] ?? []))));
            if (isset($data['experience'][$i]) && $points) {
                $data['experience'][$i]['points'] = $points;
            }
        }

        return [$data, $result['provider']];
    }

    /** Render the ATS template to a PDF file. */
    public static function render(array $data): string
    {
        File::ensureDirectoryExists(public_path(self::DIR));

        Pdf::loadView('resume.ats', ['r' => $data])
            ->setPaper('a4')
            ->setOption(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => false])
            ->save(public_path(self::GENERATED));

        return self::GENERATED;
    }

    private static function extractJson(string $text): ?array
    {
        $text = trim(preg_replace('/^```(?:json)?|```$/m', '', trim($text)));
        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start === false || $end === false) {
            return null;
        }
        try {
            $decoded = json_decode(substr($text, $start, $end - $start + 1), true, 32, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }

    private static function labels(string $locale): array
    {
        return $locale === 'id' ? [
            'summary' => 'Ringkasan Profesional', 'core' => 'Keahlian Inti', 'experience' => 'Pengalaman Kerja',
            'skills' => 'Keahlian', 'education' => 'Pendidikan', 'certs' => 'Sertifikasi', 'badges' => 'Lencana Digital',
            'pubs' => 'Publikasi', 'tools' => 'Alat', 'credential' => 'Kredensial',
        ] : [
            'summary' => 'Professional Summary', 'core' => 'Core Skills', 'experience' => 'Work Experience',
            'skills' => 'Skills', 'education' => 'Education', 'certs' => 'Certifications', 'badges' => 'Digital Badges',
            'pubs' => 'Publications', 'tools' => 'Tools', 'credential' => 'Credential',
        ];
    }
}
