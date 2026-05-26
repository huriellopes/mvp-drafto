<?php

declare(strict_types=1);

return [

    'user_status' => [
        'active' => 'Ativo',
        'suspended' => 'Suspenso',
        'pending' => 'Pendente',
        'inactive' => 'Inativo',
        'banned' => 'Banido',
    ],

    'theme' => [
        'light' => 'Claro',
        'dark' => 'Escuro',
        'system' => 'Sistema',
    ],

    'role' => [
        'super_admin' => 'Super Administrador',
        'writer' => 'Escritor',
        'reader' => 'Leitor',
    ],

    'report_status' => [
        'pending' => 'Pendente',
        'reviewed' => 'Revisado',
        'dismissed' => 'Descartado',
        'action_taken' => 'Ação tomada',
    ],

    'report_reason' => [
        'spam' => 'Spam',
        'abuse' => 'Abuso',
        'harassment' => 'Assédio',
        'plagiarism' => 'Plágio',
        'inappropriate' => 'Conteúdo inadequado',
        'other' => 'Outro',
    ],

    'post_visibility' => [
        'public' => 'Público',
        'unlisted' => 'Não listado',
        'followers_only' => 'Apenas seguidores',
    ],

    'post_status' => [
        'draft' => 'Rascunho',
        'published' => 'Publicado',
        'archived' => 'Arquivado',
        'scheduled' => 'Agendado',
    ],

    'post_type' => [
        'post' => 'Post',
        'article' => 'Artigo',
    ],

    'comment_status' => [
        'visible' => 'Visível',
        'hidden' => 'Oculto',
        'pending' => 'Pendente',
    ],

];
