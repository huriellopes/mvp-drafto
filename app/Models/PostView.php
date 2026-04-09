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
     * Accessor Inteligente: Tenta descriptografar, se falhar (ou se for um hash SHA),
     * retorna o valor original ou um fallback.
     */
    protected function ipHash(): Attribute
    {
        return Attribute::make(
            get: function (?string $value) {
                if (!$value) {
                    return '0.0.0.0';
                }

                // Se o valor não parecer um payload encriptado (não tem o formato JSON/Base64 do Laravel)
                if (!str_contains($value, 'iv')) {
                    // Pode ser um hash SHA256 do Seeder, retornamos os primeiros 8 caracteres por privacidade
                    return mb_strlen($value) > 32 ? mb_substr($value, 0, 12) . '...' : $value;
                }

                try {
                    return Crypt::decryptString($value);
                } catch (DecryptException) {
                    // Fallback para quando a APP_KEY mudou ou o dado está corrompido
                    return 'Hashed: ' . mb_substr($value, 0, 8);
                }
            },
            // Garante que ao salvar um novo, ele sempre use a criptografia atual
            set: fn (string $value) => Crypt::encryptString($value),
        );
    }

    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
            //            'ip_hash' => 'encrypted'
        ];
    }
}
