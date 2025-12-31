<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Locale;
use App\Models\ThreadMessage;
use App\Services\AITranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TranslateMessageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public ThreadMessage $message
    ) {
    }

    public function handle(AITranslationService $translationService): void
    {
        // Only translate original messages
        if (! $this->message->isOriginal()) {
            return;
        }

        // Get active locales except the source locale
        $targetLocales = Locale::where('is_active', true)
            ->where('code', '!=', $this->message->locale)
            ->pluck('code');

        foreach ($targetLocales as $targetLocale) {
            try {
                // Check if translation already exists
                $existingTranslation = ThreadMessage::where('original_ref', $this->message->id)
                    ->where('locale', $targetLocale)
                    ->first();

                if ($existingTranslation) {
                    continue;
                }

                // Translate the content
                $translatedContent = $translationService->translate(
                    $this->message->content,
                    $this->message->locale,
                    $targetLocale
                );

                // Create translation message
                ThreadMessage::create([
                    'thread_id' => $this->message->thread_id,
                    'author_id' => $this->message->author_id,
                    'author_type' => 'system',
                    'content' => $translatedContent,
                    'locale' => $targetLocale,
                    'visibility' => $this->message->visibility,
                    'original_ref' => $this->message->id,
                ]);

                Log::info("Translated message {$this->message->id} to {$targetLocale}");
            } catch (\Exception $e) {
                Log::error("Failed to translate message {$this->message->id} to {$targetLocale}: ".$e->getMessage());
                
                // Re-throw to trigger retry
                throw $e;
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Translation job failed for message {$this->message->id}: ".$exception->getMessage());
    }
}
