<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Plataformas de Links do Perfil
    |--------------------------------------------------------------------------
    |
    | Fonte da verdade para as redes sociais / tipos de link que o usuário pode
    | cadastrar no perfil. Cada chave deve corresponder a um case do enum
    | App\Enums\SocialPlatformEnum.
    |
    | - label: nome exibido (select do editor e tooltip público)
    | - icon:  ícone Lucide (mallardduck/blade-lucide-icons), sem o prefixo "lucide-"
    | - color: cor fixa da marca (NÃO segue a customização de cores da página)
    |
    | "website" é o fallback genérico para links de página avulsos.
    |
    */

    'platforms' => [
        'instagram' => ['label' => 'Instagram', 'icon' => 'instagram', 'color' => '#E4405F'],
        'facebook' => ['label' => 'Facebook', 'icon' => 'facebook', 'color' => '#1877F2'],
        'twitter' => ['label' => 'X / Twitter', 'icon' => 'twitter', 'color' => '#000000'],
        'linkedin' => ['label' => 'LinkedIn', 'icon' => 'linkedin', 'color' => '#0A66C2'],
        'youtube' => ['label' => 'YouTube', 'icon' => 'youtube', 'color' => '#FF0000'],
        'github' => ['label' => 'GitHub', 'icon' => 'github', 'color' => '#181717'],
        'tiktok' => ['label' => 'TikTok', 'icon' => 'music', 'color' => '#000000'],
        'website' => ['label' => 'Site / Outro', 'icon' => 'link', 'color' => '#4F46E5'],
    ],

];
