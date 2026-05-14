<?php

declare(strict_types=1);

namespace App\Enums;

enum ModuleEnum: string
{
    public function label(): string
    {
        return match ($this) {
            self::PROFILE => trans('dashboard.modules.items.profile'),
            self::PROFILE_BADGE => __('dashboard.modules.items.profile_badge'),
            self::ACCOUNT => trans('dashboard.modules.items.account'),
            self::MY_POSTS => trans('dashboard.modules.items.my_posts'),
            self::DRAFT => trans('dashboard.modules.items.draft'),
            self::FOLLOWS => trans('dashboard.modules.items.follows'),
            self::SAVED_POST => trans('dashboard.modules.items.saved_post'),
            self::COMMENTS => trans('dashboard.modules.items.comments'),
            self::SUPPORT => __('dashboard.modules.items.support'),
        };
    }

    case PROFILE = 'profile';
    case PROFILE_BADGE = 'profile_badge';
    case ACCOUNT = 'account';
    case MY_POSTS = 'my_posts';
    case DRAFT = 'draft';
    case FOLLOWS = 'follows';
    case SAVED_POST = 'saved_post';
    case COMMENTS = 'comments';
    case SUPPORT = 'support';
}
