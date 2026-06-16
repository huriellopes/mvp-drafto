<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Upload de mídia do editor (imagem/vídeo)
    |--------------------------------------------------------------------------
    |
    | Fonte ÚNICA do limite de upload, usada tanto na validação do servidor
    | quanto no editor (cliente). Mantenha alinhado ao PHP do servidor
    | (post_max_size / upload_max_filesize). Para vídeos maiores, use links
    | (YouTube/Vimeo).
    */
    'max_upload_kb' => (int) env('EDITOR_MAX_UPLOAD_KB', 102400), // 100 MB

    'upload_disk' => env('EDITOR_UPLOAD_DISK', 'public'),
    'upload_path' => env('EDITOR_UPLOAD_PATH', 'trix-attachments'),

    'allowed_mimetypes' => [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
        'video/mp4',
        'video/webm',
        'video/ogg',
        'video/quicktime',
    ],
];
