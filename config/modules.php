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
            'max_bio_length' => [
                'free' => 160,
                'plus' => 500,
                'pro' => 1000,
            ],
            'allow_custom_colors' => [
                'free' => false,
                'plus' => true,
                'pro' => true,
            ],
            'enable_seo' => [
                'free' => false,
                'plus' => true,
                'pro' => true,
            ],
            'show_metrics' => true,
        ],
    ],
    [
        'slug' => ModuleEnum::PROFILE_BADGE,
        'name' => 'Crachá do Escritor',
        'description' => 'Gerador de cards de identidade portáteis para compartilhamento.',
        'icon' => 'badge-check',
        'settings' => [
            'render_quality_ratio' => [
                'free' => 2,
                'plus' => 3,
                'pro' => 4,
            ],
            'allow_iframe_embed' => [
                'free' => false,
                'plus' => true,
                'pro' => true,
            ],
            'themes_available' => [
                'free' => ['light', 'dark'],
                'plus' => ['light', 'dark', 'brand'],
                'pro' => ['all'],
            ],
        ],
    ],
    [
        'slug' => ModuleEnum::MY_POSTS,
        'name' => 'Meus Artigos',
        'description' => 'Gestão de publicações e obras autorais.',
        'icon' => 'library',
        'settings' => [
            'max_posts_per_month' => [
                'free' => 5,
                'plus' => 25,
                'pro' => -1,
            ],
        ],
    ],
    [
        'slug' => ModuleEnum::DRAFT,
        'name' => 'Rascunhos',
        'description' => 'Área de escrita para manuscritos em andamento.',
        'icon' => 'file-text',
        'settings' => [
            'max_simultaneous_drafts' => [
                'free' => 3,
                'plus' => 15,
                'pro' => -1,
            ],
        ],
    ],
    [
        'slug' => ModuleEnum::COMMENTS,
        'name' => 'Comentários',
        'description' => 'Sistema de interação e feedback nas publicações.',
        'icon' => 'message-square',
        'settings' => [
            'allow_images' => [
                'free' => false,
                'plus' => false,
                'pro' => true,
            ],
            'moderation_tools' => [
                'free' => false,
                'plus' => true,
                'pro' => true,
            ],
        ],
    ],
    [
        'slug' => ModuleEnum::SUBSCRIPTIONS,
        'name' => 'Assinatura e Planos',
        'description' => 'Habilita a seção de assinatura e planos',
        'icon' => 'credit-card',
        'settings' => [],
    ],
    [
        'slug' => ModuleEnum::CATEGORIES,
        'name' => 'Categorias',
        'description' => 'Ambiente de categorias próprias.',
        'icon' => 'folder-open',
        'settings' => [
            'max_categories' => [
                'free' => 3,
                'plus' => 10,
                'pro' => -1,
            ],
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
            'two_factor_available' => [
                'free' => false,
                'plus' => true,
                'pro' => true,
            ],
        ],
    ],
    [
        'slug' => ModuleEnum::SAVED_POST,
        'name' => 'Itens Salvos',
        'description' => 'Biblioteca pessoal de conteúdos favoritos.',
        'icon' => 'bookmark',
        'settings' => [
            'max_saved_items' => [
                'free' => 50,
                'plus' => 500,
                'pro' => -1,
            ],
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
    [
        'slug' => ModuleEnum::TAGS,
        'name' => 'Sistema de Tags',
        'description' => 'Permite aos usuários organizar conteúdos com etiquetas personalizadas.',
        'icon' => 'tags',
        'settings' => [
            'allow_custom_tags' => [
                'free' => false,
                'plus' => true,
                'pro' => true,
            ],
            'max_tags_per_post' => [
                'free' => 3,
                'plus' => 10,
                'pro' => 25,
            ],
        ],
    ],
];
