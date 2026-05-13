<?php

declare(strict_types=1);

return [
    'email' => env('SUPPORT_EMAIL', 'support@drafto.pro'),
    'whatsapp' => [
        'number' => env('SUPPORT_WHATSAPP_NUMBER', '556199712493'),
        'message' => env('SUPPORT_WHATSAPP_MESSAGE', 'Olá, vim pelo dashboard da plataforma do Drafto, preciso de um suporte, pode me ajudar?'),
    ],
];
