<?php

declare(strict_types=1);

return [
    'enabled' => env('AI_ENABLED', true),
    'provider' => env('AI_PROVIDER', 'openai'),
    'api_key' => env('AI_API_KEY'),
    'model' => env('AI_MODEL', 'gpt-4'),
    'temperature' => env('AI_TEMPERATURE', 0.3),
];
