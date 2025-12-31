<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'old_content',
        'new_content',
        'revised_by',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(ThreadMessage::class, 'message_id');
    }

    public function reviser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revised_by');
    }
}
