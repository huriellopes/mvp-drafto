<?php

declare(strict_types=1);

return [
    'free' => [
        'name' => 'Free',
        'price' => 0,
        'stripe_id' => null,
        'features' => [
            'Até 5 posts mensais',
            '3 rascunhos ativos',
            'Perfil público básico',
            'Crachá em baixa resolução',
        ],
    ],
    'plus' => [
        'name' => 'Plus',
        'price' => 29.90,
        'stripe_id' => env('STRIPE_PLUS_PRICE_ID'),
        'features' => [
            'Até 20 posts mensais',
            '15 rascunhos ativos',
            'Cores customizadas no perfil',
            'Crachá com cores da marca',
            'Métricas básicas por post',
        ],
    ],
    'pro' => [
        'name' => 'Pro',
        'price' => 79.90,
        'stripe_id' => env('STRIPE_PRO_PRICE_ID'),
        'features' => [
            'Posts e Rascunhos ilimitados',
            'Métricas avançadas',
            'Crachá em Ultra-HD (4x)',
            'Ferramentas de moderação',
            'Suporte prioritário',
        ],
    ],
];
