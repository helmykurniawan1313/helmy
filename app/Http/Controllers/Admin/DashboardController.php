<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Sections;
use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Support\PortfolioContent;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $p = PortfolioContent::for('en');

        $stats = [
            ['label' => 'Experience', 'value' => count($p['experiences']), 'icon' => 'briefcase', 'color' => 'purple', 'section' => 'experiences'],
            ['label' => 'Skills', 'value' => count($p['skills']), 'icon' => 'tools', 'color' => 'blue', 'section' => 'skills'],
            ['label' => 'Portfolio', 'value' => count($p['projects']), 'icon' => 'collection-play', 'color' => 'green', 'section' => 'projects'],
            ['label' => 'Credentials', 'value' => count($p['certificates']) + count($p['badges'] ?? []), 'icon' => 'award', 'color' => 'red', 'section' => 'certificates'],
        ];

        // Which sections have been edited here (vs. still using the file defaults)
        $edited = Content::query()->get(['section', 'locale', 'updated_at'])->groupBy('section');

        return view('admin.dashboard', [
            'p' => $p,
            'stats' => $stats,
            'groups' => Sections::grouped(),
            'edited' => $edited,
            'lastEdit' => Content::query()->latest('updated_at')->first(),
            'assistant' => [
                'gemini' => (bool) config('services.gemini.key'),
                'groq' => (bool) config('services.groq.key'),
                'openrouter' => (bool) config('services.openrouter.key'),
                'order' => config('services.assistant.providers'),
            ],
        ]);
    }
}
