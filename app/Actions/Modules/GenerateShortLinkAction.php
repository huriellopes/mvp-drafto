<?php

declare(strict_types=1);

namespace App\Actions\Modules;

use App\Enums\ModuleEnum;
use App\Models\Post;
use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class GenerateShortLinkAction
{
    /**
     * Sênior: Gera ou recupera um link encurtado para um modelo (Post ou User).
     * Garante que o usuário tenha o módulo ativado antes de prosseguir.
     */
    public function exec(User $user, Model $shortable): string
    {
        // 1. Verifica se o autor (dono do conteúdo) tem o módulo ativado
        $owner = $shortable instanceof User ? $shortable : ($shortable->author ?? $user);

        if (!$owner->isModuleAvailable(ModuleEnum::LINK_SHORTENER)) {
            return $this->getOriginalUrl($shortable);
        }

        // 2. Verifica as configurações granulares (Perfil vs Posts)
        $isProfile = $shortable instanceof User;
        $isPost = $shortable instanceof Post;

        if ($isProfile && !$owner->getModuleSetting(ModuleEnum::LINK_SHORTENER, 'enable_for_profile', true)) {
            return $this->getOriginalUrl($shortable);
        }

        if ($isPost && !$owner->getModuleSetting(ModuleEnum::LINK_SHORTENER, 'enable_for_posts', true)) {
            return $this->getOriginalUrl($shortable);
        }

        // 3. Tenta encontrar um link já existente para este modelo e usuário
        $shortLink = ShortLink::query()
            ->where('user_id', $owner->id)
            ->where('shortable_type', $shortable->getMorphClass())
            ->where('shortable_id', $shortable->id)
            ->first();

        if ($shortLink) {
            return route('shortlink.redirect', $shortLink->code);
        }

        // 3. Gera um novo código único
        $code = $this->generateUniqueCode();

        $shortLink = ShortLink::create([
            'user_id' => $owner->id,
            'shortable_type' => $shortable->getMorphClass(),
            'shortable_id' => $shortable->id,
            'code' => $code,
        ]);

        return route('shortlink.redirect', $shortLink->code);
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = Str::random(6);
        } while (ShortLink::where('code', $code)->exists());

        return $code;
    }

    private function getOriginalUrl(Model $shortable): string
    {
        if ($shortable instanceof User) {
            return route('profile.show', $shortable->profile->username);
        }

        if ($shortable instanceof Post) {
            return route('posts.show', $shortable->slug);
        }

        return url('/');
    }
}
