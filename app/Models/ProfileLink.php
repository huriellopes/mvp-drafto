<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SocialPlatformEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileLink extends Model
{
    protected $fillable = [
        'profile_id',
        'platform',
        'url',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Plataforma como enum, com fallback seguro para valores desconhecidos.
     */
    public function platformEnum(): SocialPlatformEnum
    {
        return SocialPlatformEnum::tryFrom((string) $this->platform) ?? SocialPlatformEnum::fallback();
    }

    /**
     * Nome do ícone Lucide para a plataforma.
     */
    public function icon(): string
    {
        return $this->platformEnum()->icon();
    }

    /**
     * Cor fixa da marca da plataforma.
     */
    public function brandColor(): string
    {
        return $this->platformEnum()->color();
    }

    /**
     * Rótulo de exibição (nome da plataforma).
     */
    public function label(): string
    {
        return $this->platformEnum()->label();
    }
}
