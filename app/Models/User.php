<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'external_user_id',
        'name',
        'email',
        'avatar',
        'preferred_locale',
        'timezone',
        'date_format',
        'is_staff',
        'is_admin',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'is_staff' => 'boolean',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    public function threads(): HasMany
    {
        return $this->hasMany(Thread::class, 'owner_id');
    }

    public function assignedThreads(): HasMany
    {
        return $this->hasMany(Thread::class, 'assigned_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ThreadMessage::class, 'author_id');
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(UserLicense::class);
    }

    public function participatingThreads(): BelongsToMany
    {
        return $this->belongsToMany(Thread::class, 'thread_participants')
            ->withTimestamps()
            ->withPivot('joined_at');
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->is_admin) {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('name', $permission);
            })
            ->exists();
    }

    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->is_admin) {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissions) {
                $query->whereIn('name', $permissions);
            })
            ->exists();
    }

    public function hasAllPermissions(array $permissions): bool
    {
        if ($this->is_admin) {
            return true;
        }

        foreach ($permissions as $permission) {
            if (! $this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }
}
