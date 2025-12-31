<?php

declare(strict_types=1);

namespace App\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool any(array $permissions)
 * @method static bool all(array $permissions)
 * @method static bool check(string $permission)
 *
 * @see \App\Support\AuthorizationManager
 */
class Authorize extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'authorize';
    }
}
