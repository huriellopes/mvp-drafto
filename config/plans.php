<?php

declare(strict_types=1);

return [
    'free' => [
        'name' => 'Free',
        'slug' => 'free',
        'price' => 0,
        'stripe_id' => null,
        'features' => [
            '5 publicações por mês',
            '3 rascunhos simultâneos',
            'Perfil público padrão',
            'Crachá básico (Marca d\'água Drafto)',
            'Comunidade de leitores',
        ],
    ],
    'plus' => [
        'name' => 'Plus',
        'slug' => 'plus',
        'price' => 29.90,
        'stripe_id' => env('STRIPE_PLUS_PRICE_ID'),
        'features' => [
            '25 publicações por mês',
            '15 rascunhos simultâneos',
            'Identidade visual customizada',
            'Crachá HD sem marca d\'água',
            'Estatísticas de visualizações',
            'Newsletter para seguidores',
        ],
    ],
    'pro' => [
        'name' => 'Pro',
        'slug' => 'pro',
        'price' => 79.90,
        'stripe_id' => env('STRIPE_PRO_PRICE_ID'),
        'features' => [
            'Publicações ilimitadas',
            'Rascunhos ilimitados',
            'Domínio customizado (em breve)',
            'Estatísticas avançadas e retenção',
            'Crachá Ultra-HD para impressão',
            'Ferramentas de moderação de comentários',
            'Selo de Escritor Verificado',
        ],
    ],
];
