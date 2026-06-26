<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class MagicLoginToken extends Model
{
    /**
     * Tempo de validade do link mágico, em minutos.
     */
    public const EXPIRES_MINUTES = 15;

    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
        'remember',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'remember' => 'boolean',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Gera o hash determinístico armazenado no banco para um token em texto puro.
     */
    public static function hashToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    public static function expiresAt(): Carbon
    {
        return Carbon::now()->addMinutes(self::EXPIRES_MINUTES);
    }
}
