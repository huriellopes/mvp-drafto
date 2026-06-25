<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumOptions;

enum SocialPlatformEnum: string
{
    use EnumOptions;

    /**
     * Plataforma genérica usada como fallback para qualquer valor desconhecido.
     */
    public static function fallback(): self
    {
        return self::WEBSITE;
    }

    public function label(): string
    {
        return $this->meta('label');
    }

    public function icon(): string
    {
        return $this->meta('icon');
    }

    /**
     * Cor fixa da marca (sobrescreve o color() padrão do trait EnumOptions).
     */
    public function color(): string
    {
        return $this->meta('color');
    }

    /**
     * Lê os metadados da plataforma na config, com fallback para "website".
     */
    private function meta(string $key): string
    {
        $platforms = config('profile_links.platforms', []);

        return $platforms[$this->value][$key]
            ?? $platforms[self::WEBSITE->value][$key]
            ?? '';
    }

    case INSTAGRAM = 'instagram';
    case FACEBOOK = 'facebook';
    case TWITTER = 'twitter';
    case LINKEDIN = 'linkedin';
    case YOUTUBE = 'youtube';
    case GITHUB = 'github';
    case TIKTOK = 'tiktok';
    case WEBSITE = 'website';
}
