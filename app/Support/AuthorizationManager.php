<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Auth;

class AuthorizationManager
{
    public function any(array $permissions): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $user->hasAnyPermission($permissions);
    }

    public function all(array $permissions): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $user->hasAllPermissions($permissions);
    }

    public function check(string $permission): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $user->hasPermission($permission);
    }
}
