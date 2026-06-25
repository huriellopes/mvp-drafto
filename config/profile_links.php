<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Plataformas de Links do Perfil
    |--------------------------------------------------------------------------
    |
    | Identidade visual (ícone + cor da marca) das redes sociais / tipos de link
    | que o usuário pode cadastrar. Cada chave deve corresponder a um case do enum
    | App\Enums\SocialPlatformEnum. Os RÓTULOS são traduzíveis e ficam em
    | lang/{locale}/enums.php (chave "social_platform").
    |
    | - icon:  ícone Lucide (mallardduck/blade-lucide-icons), sem o prefixo "lucide-"
    | - color: cor fixa da marca (NÃO segue a customização de cores da página)
    |
    | "website" é o fallback genérico para links de página avulsos.
    |
    */

    'platforms' => [
        'instagram' => ['icon' => 'instagram', 'color' => '#E4405F'],
        'facebook' => ['icon' => 'facebook', 'color' => '#1877F2'],
        'twitter' => ['icon' => 'twitter', 'color' => '#000000'],
        'linkedin' => ['icon' => 'linkedin', 'color' => '#0A66C2'],
        'youtube' => ['icon' => 'youtube', 'color' => '#FF0000'],
        'github' => ['icon' => 'github', 'color' => '#181717'],
        'tiktok' => ['icon' => 'music', 'color' => '#000000'],
        'website' => ['icon' => 'link', 'color' => '#4F46E5'],
    ],

];
