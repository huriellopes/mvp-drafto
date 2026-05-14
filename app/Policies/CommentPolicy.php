<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\CommentStatusEnum;
use App\Enums\ModuleEnum;
use App\Enums\RoleEnum;
use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole(RoleEnum::SUPER_ADMIN) ? true : null;
    }

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Comment $comment): bool
    {
        return $comment->status === CommentStatusEnum::APPROVED;
    }

    public function create(?User $user): bool
    {
        return $user ? $user->isActive() : true;
    }

    public function reply(?User $user, Comment $parentComment): bool
    {
        if ($user && !$user->isActive()) {
            return false;
        }

        $postAuthor = $parentComment->post->author;
        $maxDepth = $postAuthor->getModuleSetting(ModuleEnum::COMMENTS, 'max_depth', 3);

        // Se depth não existir, assumimos que pode (ou implementamos lógica simples)
        // Como o sistema parece usar 3 níveis no withRelations, vamos limitar a 3 se depth existisse
        // Para simplificar agora, vamos apenas permitir se o post permitir
        return $parentComment->post->comments_enabled;
    }

    public function update(User $user, Comment $comment): bool
    {
        return $user->isActive()
            && $comment->user_id === $user->id
            && $comment->status === CommentStatusEnum::APPROVED;
    }

    public function delete(User $user, Comment $comment): bool
    {
        // Dono do comentário OU dono do Post (moderador do próprio espaço) podem deletar
        $isOwner = $comment->user_id === $user->id;
        $isPostAuthor = $comment->post->user_id === $user->id;

        return $user->isActive() && ($isOwner || $isPostAuthor);
    }

    public function restore(User $user, Comment $comment): bool
    {
        return false;
    }

    public function forceDelete(User $user, Comment $comment): bool
    {
        return false;
    }

    public function like(User $user, Comment $comment): bool
    {
        return $user->isActive()
            && $comment->status === CommentStatusEnum::APPROVED;
    }

    public function moderate(User $user, Comment $comment): bool
    {
        // Apenas o autor do Post pode moderar (ocultar) comentários de terceiros
        // E apenas se a configuração do módulo permitir ferramentas de moderação
        return $user->id === $comment->post->user_id
            && $user->getModuleSetting(ModuleEnum::COMMENTS, 'moderation_tools', false);
    }
}
