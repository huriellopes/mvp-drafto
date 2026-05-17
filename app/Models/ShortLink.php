<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ShortLinkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ShortLink extends Model
{
    /** @use HasFactory<ShortLinkFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shortable_type',
        'shortable_id',
        'code',
        'clicks',
    ];

    /**
     * Get the parent shortable model (Post or User).
     */
    public function shortable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who owns the short link.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
