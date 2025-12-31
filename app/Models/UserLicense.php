<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLicense extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'external_license_id',
        'product_name',
        'item_id',
        'domain',
        'purchased_at',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'purchased_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return $this->is_active && ! $this->isExpired();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
