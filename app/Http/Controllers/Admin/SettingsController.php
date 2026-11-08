<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PortfolioAssistant;
use App\Support\Settings;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class SettingsController extends Controller
{
    private const PROVIDERS = [
        'gemini' => ['name' => 'Google Gemini', 'console' => 'https://aistudio.google.com/apikey', 'placeholder' => 'AIza…', 'note' => 'Detailed answers, ~3–5 s.'],
        'groq' => ['name' => 'Groq', 'console' => 'https://console.groq.com/keys', 'placeholder' => 'gsk_…', 'note' => 'Very fast, ~1 s.'],
        'openrouter' => ['name' => 'OpenRouter', 'console' => 'https://openrouter.ai/settings/keys', 'placeholder' => 'sk-or-…', 'note' => 'One key for hundreds of models (OpenAI, Anthropic, Meta…). Model IDs look like provider/model.'],
    ];

    public function edit(): View
    {
        $saved = Settings::all();
        $order = $this->order();

        $providers = [];
        foreach (self::PROVIDERS as $id => $info) {
            $position = array_search($id, $order, true);
            $providers[$id] = $info + [
                'mask' => Settings::mask(config("services.$id.key")),
                'source' => Settings::source("{$id}_key"),
                'model' => $saved["{$id}_model"] ?? '',
                'default_model' => Settings::fileDefault("{$id}_model"),
                'priority' => $position === false ? 0 : $position + 1,
            ];
        }

        return view('admin.settings.ai', ['providers' => $providers]);
    }

    public function update(Request $request): RedirectResponse
    {
        $ids = array_keys(self::PROVIDERS);
        $rules = ['clear' => ['array'], 'clear.*' => ['in:'.implode(',', array_map(fn ($id) => "{$id}_key", $ids))]];
        $names = [];

        foreach ($ids as $id) {
            $rules["{$id}_key"] = ['nullable', 'string', 'max:300'];
            $rules["{$id}_model"] = ['nullable', 'string', 'max:150', 'regex:/^[\w.\/:@-]+$/'];
            $rules["priority.$id"] = ['required', 'integer', 'between:0,3'];
            $names["{$id}_key"] = self::PROVIDERS[$id]['name'].' key';
            $names["{$id}_model"] = self::PROVIDERS[$id]['name'].' model';
        }

        $data = $request->validate($rules, ['regex' => 'The :attribute may only contain letters, numbers and . / : @ - _'], $names);

        // Order: lowest number first; 0 means "off"
        $priority = array_filter($data['priority'], fn ($p) => (int) $p > 0);
        if ($priority === []) {
            return back()->withInput()->withErrors(['priority' => 'Turn on at least one provider, or the chat on your website will stop answering.']);
        }
        uksort($priority, fn ($a, $b) => [$priority[$a], array_search($a, $ids)] <=> [$priority[$b], array_search($b, $ids)]);

        foreach ($ids as $id) {
            // A blank key field keeps the current key; the checkbox removes it
            if (in_array("{$id}_key", $data['clear'] ?? [], true)) {
                Settings::put("{$id}_key", null);
            } elseif (filled($data["{$id}_key"] ?? null)) {
                Settings::put("{$id}_key", trim($data["{$id}_key"]));
            }
            Settings::put("{$id}_model", trim((string) ($data["{$id}_model"] ?? '')));
        }
        Settings::put('assistant_providers', implode(',', array_keys($priority)));

        return back()->with('status', 'AI settings saved. Order: '.implode(' → ', array_map(fn ($id) => self::PROVIDERS[$id]['name'], array_keys($priority))).'.');
    }

    /** Try a provider with the saved settings and report the result. */
    public function test(string $provider, PortfolioAssistant $assistant): RedirectResponse
    {
        abort_unless(isset(self::PROVIDERS[$provider]), 404);
        $name = self::PROVIDERS[$provider]['name'];

        if (! config("services.$provider.key")) {
            return back()->withErrors(['test' => "$name: no API key set."]);
        }

        try {
            $start = microtime(true);
            $reply = $assistant->ping($provider);
            $ms = (int) round((microtime(true) - $start) * 1000);

            return back()->with('status', "$name works ({$ms} ms, model ".config("services.$provider.model").'). Reply: "'.Str::limit($reply, 40).'"');
        } catch (RequestException $e) {
            $message = $e->response->json('error.message') ?? $e->response->json('error') ?? $e->getMessage();

            return back()->withErrors(['test' => "$name failed (HTTP {$e->response->status()}): ".Str::limit(is_string($message) ? $message : json_encode($message), 200)]);
        } catch (Throwable $e) {
            return back()->withErrors(['test' => "$name failed: ".Str::limit($e->getMessage(), 200)]);
        }
    }

    /** @return array<int, string> */
    private function order(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) config('services.assistant.providers')))));
    }
}
