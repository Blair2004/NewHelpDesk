<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ThreadMessage extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'thread_id',
        'author_id',
        'author_type',
        'content',
        'locale',
        'visibility',
        'original_ref',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function originalMessage(): BelongsTo
    {
        return $this->belongsTo(ThreadMessage::class, 'original_ref');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ThreadMessage::class, 'original_ref');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(MessageRevision::class, 'message_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MessageAttachment::class, 'message_id');
    }

    public function isOriginal(): bool
    {
        return $this->original_ref === null;
    }

    public function isTranslation(): bool
    {
        return $this->original_ref !== null;
    }

    public function isSensitive(): bool
    {
        return $this->visibility === 'sensitive';
    }
}
