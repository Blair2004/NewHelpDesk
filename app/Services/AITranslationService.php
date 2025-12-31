<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AITranslationService
{
    public function translate(string $text, string $sourceLocale, string $targetLocale): string
    {
        if (! config('ai.enabled')) {
            throw new \Exception('AI translation is disabled');
        }

        $provider = config('ai.provider');

        return match ($provider) {
            'openai' => $this->translateWithOpenAI($text, $sourceLocale, $targetLocale),
            default => throw new \Exception("Unsupported AI provider: {$provider}"),
        };
    }

    private function translateWithOpenAI(string $text, string $sourceLocale, string $targetLocale): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.config('ai.api_key'),
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => config('ai.model'),
            'temperature' => config('ai.temperature'),
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "You are a professional translator. Translate the following text from {$sourceLocale} to {$targetLocale}. Preserve formatting, tone, and meaning. Return only the translated text without any additional commentary.",
                ],
                [
                    'role' => 'user',
                    'content' => $text,
                ],
            ],
        ]);

        if (! $response->successful()) {
            throw new \Exception('Failed to translate text: '.$response->body());
        }

        $data = $response->json();

        return $data['choices'][0]['message']['content'] ?? '';
    }

    public function getAvailableLocales(): array
    {
        return [
            'en' => 'English',
            'es' => 'Spanish',
            'fr' => 'French',
            'de' => 'German',
            'it' => 'Italian',
            'pt' => 'Portuguese',
            'ru' => 'Russian',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'zh' => 'Chinese',
            'ar' => 'Arabic',
        ];
    }
}
