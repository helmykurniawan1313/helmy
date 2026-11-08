<?php

namespace App\Services;

use App\Support\PortfolioContent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Answers visitor questions using only the portfolio data.
 * Tries providers in the order set in the admin (or ASSISTANT_PROVIDERS).
 */
class PortfolioAssistant
{
    public const PROVIDERS = ['gemini', 'groq', 'openrouter'];

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{reply: string, provider: string}
     */
    public function reply(array $messages, string $locale): array
    {
        $system = $this->systemPrompt($locale);
        $errors = [];

        foreach ($this->providers() as $provider) {
            if (! config("services.{$provider}.key")) {
                continue;
            }

            try {
                $reply = $this->ask($provider, $system, $messages);

                if (trim($reply) !== '') {
                    return ['reply' => trim($reply), 'provider' => $provider];
                }
            } catch (Throwable $e) {
                $errors[] = "{$provider}: {$e->getMessage()}";
            }
        }

        Log::warning('Portfolio assistant failed', ['errors' => $errors]);

        throw new RuntimeException('No AI provider available.');
    }

    /** Per-call overrides for long jobs (e.g. resume writing). */
    private array $limits = ['tokens' => null, 'timeout' => null];

    /**
     * One-off completion with the same provider order and fallback as the chat,
     * but with room for long output.
     *
     * @return array{text: string, provider: string}
     */
    public function complete(string $system, string $prompt, int $maxTokens = 6000, int $timeout = 90): array
    {
        $this->limits = ['tokens' => $maxTokens, 'timeout' => $timeout];
        $errors = [];

        try {
            foreach ($this->providers() as $provider) {
                if (! config("services.{$provider}.key")) {
                    continue;
                }
                try {
                    $text = trim($this->ask($provider, $system, [['role' => 'user', 'content' => $prompt]]));
                    if ($text !== '') {
                        return ['text' => $text, 'provider' => $provider];
                    }
                } catch (Throwable $e) {
                    $errors[] = "{$provider}: {$e->getMessage()}";
                }
            }
        } finally {
            $this->limits = ['tokens' => null, 'timeout' => null];
        }

        Log::warning('AI completion failed', ['errors' => $errors]);

        throw new RuntimeException($errors ? 'All AI providers failed: '.implode(' | ', $errors) : 'No AI provider has a key.');
    }

    /** Send a one-word prompt to check a provider's key and model. Throws on failure. */
    public function ping(string $provider): string
    {
        $messages = [['role' => 'user', 'content' => 'Reply with the single word: OK']];

        return trim($this->ask($provider, 'You are a connectivity check.', $messages));
    }

    private function ask(string $provider, string $system, array $messages): string
    {
        return match ($provider) {
            'groq' => $this->askGroq($system, $messages),
            'gemini' => $this->askGemini($system, $messages),
            'openrouter' => $this->askOpenRouter($system, $messages),
        };
    }

    /** @return array<int, string> providers in the order to try them */
    private function providers(): array
    {
        $order = array_map('trim', explode(',', (string) config('services.assistant.providers')));

        return array_values(array_intersect($order, self::PROVIDERS));
    }

    private function askGroq(string $system, array $messages): string
    {
        $model = config('services.groq.model');
        $payload = [
            'model' => $model,
            'temperature' => 0.3,
            'max_tokens' => $this->limits['tokens'] ?? 1500,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ...$messages,
            ],
        ];

        // Reasoning models spend tokens thinking first; keep that short for a chat widget
        if (str_starts_with($model, 'openai/gpt-oss')) {
            $payload['reasoning_effort'] = 'low';
        }

        $response = Http::withToken(config('services.groq.key'))
            ->connectTimeout(5)->timeout($this->limits['timeout'] ?? 15)
            ->post('https://api.groq.com/openai/v1/chat/completions', $payload)
            ->throw();

        return (string) $response->json('choices.0.message.content', '');
    }

    /** OpenRouter: one key for many models (OpenAI-compatible API). */
    private function askOpenRouter(string $system, array $messages): string
    {
        $response = Http::withToken(config('services.openrouter.key'))
            ->withHeaders([
                // Optional attribution headers OpenRouter shows in its app rankings
                'HTTP-Referer' => config('app.url'),
                'X-Title' => config('app.name'),
            ])
            ->connectTimeout(5)
            ->timeout($this->limits['timeout'] ?? 20)
            ->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => config('services.openrouter.model'),
                'temperature' => 0.3,
                'max_tokens' => $this->limits['tokens'] ?? 1500,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ...$messages,
                ],
            ])
            ->throw();

        return (string) $response->json('choices.0.message.content', '');
    }

    private function askGemini(string $system, array $messages): string
    {
        $model = config('services.gemini.model');

        $response = Http::withHeaders(['x-goog-api-key' => config('services.gemini.key')])
            ->connectTimeout(5)->timeout($this->limits['timeout'] ?? 12)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                'system_instruction' => ['parts' => [['text' => $system]]],
                'contents' => array_map(fn ($m) => [
                    'role' => $m['role'] === 'assistant' ? 'model' : 'user',
                    'parts' => [['text' => $m['content']]],
                ], $messages),
                'generationConfig' => ['temperature' => 0.3, 'maxOutputTokens' => $this->limits['tokens'] ?? 2048],
            ])
            ->throw();

        return collect($response->json('candidates.0.content.parts', []))->pluck('text')->implode('');
    }

    private function systemPrompt(string $locale): string
    {
        $p = PortfolioContent::for($locale);

        $experience = collect($p['experiences'])->map(fn ($e) => "- {$e['title']} at {$e['org']} ({$e['period']}, {$e['type']})\n"
            .collect($e['points'])->map(fn ($pt) => "  • {$pt}")->implode("\n")
            .(empty($e['tags']) ? '' : "\n  Tools: ".implode(', ', $e['tags'])))->implode("\n");

        $skills = collect($p['skills'])->map(fn ($s) => "- {$s['name']} ({$p['levels'][$s['level']]})")->implode("\n");
        $services = collect($p['services'])->map(fn ($s) => "- {$s['title']}: {$s['desc']}")->implode("\n");
        $projects = collect($p['projects'])->map(fn ($pr) => "- {$pr['title']} for {$pr['client']} ({$pr['category']})")->implode("\n");
        $education = collect($p['education'])->map(fn ($ed) => "- {$ed['degree']}, {$ed['school']} ({$ed['period']})\n"
            .collect($ed['points'])->map(fn ($pt) => "  • {$pt}")->implode("\n"))->implode("\n");
        $certs = collect($p['certificates'])->map(fn ($c) => "- {$c['title']} – {$c['issuer']} ({$c['date']})")->implode("\n");
        $about = implode("\n", $p['banner']['about_section']['body']);

        return <<<PROMPT
        You are the assistant on {$p['name']}'s personal portfolio website. Visitors are usually recruiters, potential clients or collaborators.

        Rules:
        - Answer ONLY from the profile below. If the answer is not there, say you don't have that information and suggest contacting {$p['name']} directly.
        - Never invent employers, dates, salaries, skills or opinions. Do not discuss topics unrelated to {$p['name']}'s professional profile; politely steer back.
        - Reply in the same language the visitor writes in (Indonesian or English).
        - Be concise and friendly: 1–4 short sentences, or a short bullet list when listing things. Plain text; **bold** and "- " bullets are allowed.
        - Refer to {$p['name']} in the third person ("Helmy").

        PROFILE
        Name: {$p['name']}
        Role: {$p['role']}
        Location: {$p['location']}
        Email: {$p['email']}
        WhatsApp: +{$p['whatsapp']}
        Website: {$p['website']}

        Summary: {$p['about']}
        {$about}

        Experience (newest first):
        {$experience}

        Skills (with level):
        {$skills}

        Services offered:
        {$services}

        Projects:
        {$projects}

        Education:
        {$education}

        Certifications:
        {$certs}
        PROMPT;
    }
}
