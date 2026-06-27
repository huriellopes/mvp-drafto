<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Content Signal
    |--------------------------------------------------------------------------
    |
    | Sinaliza os usos permitidos do conteúdo (proposta de Content Signals).
    | "search=yes" libera indexação de busca; "ai-train=no" reserva os direitos
    | de treino de IA (opt-out de text-and-data-mining, Art. 4 da Diretiva
    | UE 2019/790). Aplicado ao grupo "User-agent: *".
    |
    */

    'content_signal' => env('ROBOTS_CONTENT_SIGNAL', 'search=yes,ai-train=no'),

    /*
    |--------------------------------------------------------------------------
    | Preâmbulo Jurídico
    |--------------------------------------------------------------------------
    |
    | Inclui no topo do arquivo o texto de reserva de direitos que acompanha os
    | content signals. Mantém a plataforma como fonte da verdade, espelhando o
    | que o Cloudflare Managed robots.txt adicionava na borda.
    |
    */

    'include_preamble' => (bool) env('ROBOTS_INCLUDE_PREAMBLE', true),

    /*
    |--------------------------------------------------------------------------
    | Caminhos Bloqueados (User-agent: *)
    |--------------------------------------------------------------------------
    |
    | Áreas privadas, fluxos de autenticação e endpoints sem valor de indexação.
    |
    */

    'disallow' => [
        '/dashboard/',
        '/login',
        '/register',
        '/forgot-password',
        '/reset-password',
        '/logout',
        '/email/',
        '/newsletter/',
        '/s/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Crawlers de IA Bloqueados
    |--------------------------------------------------------------------------
    |
    | Recebem um grupo próprio com "Disallow: /". Espelha a lista gerenciada do
    | Cloudflare para que o bloqueio passe a ser controlado pela plataforma.
    |
    */

    'blocked_ai_bots' => [
        'Amazonbot',
        'Applebot-Extended',
        'Bytespider',
        'CCBot',
        'ClaudeBot',
        'CloudflareBrowserRenderingCrawler',
        'Google-Extended',
        'GPTBot',
        'meta-externalagent',
    ],

];
