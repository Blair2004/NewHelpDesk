<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Thread extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'locale',
        'visibility',
        'status',
        'is_locked',
        'category_id',
        'owner_id',
        'license_id',
        'assigned_user_id',
        'assigned_department_id',
        'last_reply_at',
        'last_user_reply_at',
    ];

    protected function casts(): array
    {
        return [
            'is_locked' => 'boolean',
            'last_reply_at' => 'datetime',
            'last_user_reply_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function assignedDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'assigned_department_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(UserLicense::class, 'license_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'thread_tag');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ThreadMessage::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'thread_participants')
            ->withTimestamps()
            ->withPivot('joined_at');
    }

    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }

    public function isPrivate(): bool
    {
        return $this->visibility === 'private';
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }
}
