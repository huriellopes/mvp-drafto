<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PostViewFactory;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

#[Fillable([
    'post_id',
    'user_id',
    'session_id',
    'ip_hash',
    'user_agent',
    'viewed_at',
])]
class PostView extends Model
{
    /** @use HasFactory<PostViewFactory> */
    use HasFactory;

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function viewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Accessor e Mutator para IP com Fallback de segurança
     */
    protected function ipHash(): Attribute
    {
        return Attribute::make(
            get: function (string $value) {
                try {
                    return Crypt::decryptString($value);
                } catch (DecryptException) {
                    return $value;
                }
            },
            set: fn (string $value) => Crypt::encryptString($value),
        );
    }

    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
        ];
    }
}
