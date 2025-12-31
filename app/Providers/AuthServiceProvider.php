<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Thread;
use App\Models\ThreadMessage;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Thread permissions
        Gate::define('view-thread', function (User $user, Thread $thread) {
            if ($user->is_staff) {
                return true;
            }

            if ($thread->isPublic()) {
                return true;
            }

            return $thread->owner_id === $user->id
                || $thread->participants()->where('user_id', $user->id)->exists();
        });

        Gate::define('create-thread', function (User $user) {
            // User must be active
            if (! $user->is_active) {
                return false;
            }
            
            // Staff can always create threads
            if ($user->is_staff) {
                return true;
            }
            
            // Regular users must have at least one valid license
            return $user->licenses()->get()->contains(function ($license) {
                return $license->isValid();
            });
        });

        Gate::define('edit-thread', function (User $user, Thread $thread) {
            if ($user->is_staff && $user->hasPermission('edit-any-thread')) {
                return true;
            }

            return $thread->owner_id === $user->id && ! $thread->is_locked;
        });

        Gate::define('delete-thread', function (User $user, Thread $thread) {
            if ($user->is_staff && $user->hasPermission('delete-any-thread')) {
                return true;
            }

            return $thread->owner_id === $user->id;
        });

        // Message permissions
        Gate::define('create-message', function (User $user, Thread $thread) {
            if ($thread->is_locked && ! $user->is_staff) {
                return false;
            }

            if ($thread->isPublic()) {
                return true;
            }

            return $thread->owner_id === $user->id
                || $thread->participants()->where('user_id', $user->id)->exists()
                || $user->is_staff;
        });

        Gate::define('edit-message', function (User $user, ThreadMessage $message) {
            if ($user->is_staff && $user->hasPermission('edit-any-message')) {
                return true;
            }

            if ($message->thread->is_locked && ! $user->is_staff) {
                return false;
            }

            return $message->author_id === $user->id;
        });

        Gate::define('delete-message', function (User $user, ThreadMessage $message) {
            if ($user->is_staff && $user->hasPermission('delete-any-message')) {
                return true;
            }

            return $message->author_id === $user->id;
        });

        Gate::define('view-message-revisions', function (User $user) {
            return $user->is_staff;
        });

        // Admin permissions
        Gate::define('access-admin', function (User $user) {
            return $user->is_staff;
        });

        Gate::define('manage-settings', function (User $user) {
            return $user->hasPermission('manage-settings');
        });

        Gate::define('manage-users', function (User $user) {
            return $user->hasPermission('manage-users');
        });

        Gate::define('manage-roles', function (User $user) {
            return $user->hasPermission('manage-roles');
        });
    }
}
