<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Answers visitor questions using only the portfolio data.
 * Tries providers in the order set by ASSISTANT_PROVIDERS (default: Gemini, then Groq).
 */
class PortfolioAssistant
{
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
                $reply = $provider === 'groq'
                    ? $this->askGroq($system, $messages)
                    : $this->askGemini($system, $messages);

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

    /** @return array<int, string> providers in the order to try them */
    private function providers(): array
    {
        $order = array_map('trim', explode(',', (string) config('services.assistant.providers')));

        return array_values(array_intersect($order, ['gemini', 'groq']));
    }

    private function askGroq(string $system, array $messages): string
    {
        $model = config('services.groq.model');
        $payload = [
            'model' => $model,
            'temperature' => 0.3,
            'max_tokens' => 1500,
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
            ->timeout(20)
            ->post('https://api.groq.com/openai/v1/chat/completions', $payload)
            ->throw();

        return (string) $response->json('choices.0.message.content', '');
    }

    private function askGemini(string $system, array $messages): string
    {
        $model = config('services.gemini.model');

        $response = Http::withHeaders(['x-goog-api-key' => config('services.gemini.key')])
            ->timeout(25)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                'system_instruction' => ['parts' => [['text' => $system]]],
                'contents' => array_map(fn ($m) => [
                    'role' => $m['role'] === 'assistant' ? 'model' : 'user',
                    'parts' => [['text' => $m['content']]],
                ], $messages),
                'generationConfig' => ['temperature' => 0.3, 'maxOutputTokens' => 2048],
            ])
            ->throw();

        return collect($response->json('candidates.0.content.parts', []))->pluck('text')->implode('');
    }

    private function systemPrompt(string $locale): string
    {
        app()->setLocale($locale);
        $p = config('portfolio') + __('portfolio');

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
