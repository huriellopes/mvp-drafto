<?php

declare(strict_types=1);

use App\Enums\ModuleEnum;

return [
    [
        'slug' => ModuleEnum::PROFILE,
        'name' => 'Perfil Público',
        'description' => 'Visualização pública do autor, biografia e seus artigos publicados.',
        'icon' => 'user-circle',
        'settings' => [
            'max_bio_length' => 1000,
            'allow_custom_colors' => true,
            'enable_seo' => true,
            'show_metrics' => true,
        ],
    ],
    [
        'slug' => ModuleEnum::PROFILE_BADGE,
        'name' => 'Crachá do Escritor',
        'description' => 'Gerador de cards de identidade portáteis para compartilhamento.',
        'icon' => 'badge-check',
        'settings' => [
            'render_quality_ratio' => 4,
            'allow_iframe_embed' => true,
            'themes_available' => ['all'],
        ],
    ],
    [
        'slug' => ModuleEnum::MY_POSTS,
        'name' => 'Meus Artigos',
        'description' => 'Gestão de publicações e obras autorais.',
        'icon' => 'library',
        'settings' => [
            'max_posts_per_month' => -1,
            'enable_seo' => true,
        ],
    ],
    [
        'slug' => ModuleEnum::DRAFT,
        'name' => 'Rascunhos',
        'description' => 'Área de escrita para manuscritos em andamento.',
        'icon' => 'file-text',
        'settings' => [
            'max_simultaneous_drafts' => -1,
        ],
    ],
    [
        'slug' => ModuleEnum::COMMENTS,
        'name' => 'Comentários',
        'description' => 'Sistema de interação e feedback nas publicações.',
        'icon' => 'message-square',
        'settings' => [
            'allow_images' => true,
            'moderation_tools' => true,
            'max_depth' => 5,
        ],
    ],
    [
        'slug' => ModuleEnum::FOLLOWS,
        'name' => 'Rede de Seguidores',
        'description' => 'Sistema de conexões sociais.',
        'icon' => 'users-round',
        'settings' => [
            'notify_on_new_follower' => true,
        ],
    ],
    [
        'slug' => ModuleEnum::ACCOUNT,
        'name' => 'Configurações de Conta',
        'description' => 'Gestão de segurança e preferências.',
        'icon' => 'settings',
        'settings' => [
            'two_factor_available' => true,
        ],
    ],
    [
        'slug' => ModuleEnum::SAVED_POST,
        'name' => 'Itens Salvos',
        'description' => 'Biblioteca pessoal de conteúdos favoritos.',
        'icon' => 'bookmark',
        'settings' => [
            'max_saved_items' => -1,
        ],
    ],
    [
        'slug' => ModuleEnum::SUPPORT,
        'name' => 'Suporte',
        'description' => 'Central de ajuda e contato direto com a equipe.',
        'icon' => 'help-circle',
        'settings' => [
            'enable_contact_form' => true,
        ],
    ],
];
