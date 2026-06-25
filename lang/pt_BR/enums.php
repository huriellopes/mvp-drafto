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

    'link_visibility' => [
        'public' => 'Público',
        'private' => 'Privado (inativo)',
    ],

    'social_platform' => [
        'instagram' => 'Instagram',
        'facebook' => 'Facebook',
        'twitter' => 'X / Twitter',
        'linkedin' => 'LinkedIn',
        'youtube' => 'YouTube',
        'github' => 'GitHub',
        'tiktok' => 'TikTok',
        'website' => 'Site / Outro',
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

    'update_audience' => [
        'all' => 'Todos',
        'writers' => 'Escritores',
        'readers' => 'Leitores',
        'description' => [
            'all' => 'Todos os usuários elegíveis.',
            'writers' => 'Apenas escritores.',
            'readers' => 'Apenas leitores.',
        ],
    ],

];
