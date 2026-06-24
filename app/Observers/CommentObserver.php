<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\CommentStatusEnum;
use App\Models\Comment;

final class CommentObserver
{
    /**
     * Handle the Comment "created" event.
     */
    public function created(Comment $comment): void
    {
        if ($comment->status === CommentStatusEnum::VISIBLE) {
            $this->updateCount($comment, 1);
        }
    }

    /**
     * Handle the Comment "updated" event.
     */
    public function updated(Comment $comment): void
    {
        if ($comment->wasChanged('status')) {
            $oldStatus = $comment->getOriginal('status');
            $newStatus = $comment->status;

            if ($oldStatus !== CommentStatusEnum::VISIBLE && $newStatus === CommentStatusEnum::VISIBLE) {
                $this->updateCount($comment, 1);
            } elseif ($oldStatus === CommentStatusEnum::VISIBLE && $newStatus !== CommentStatusEnum::VISIBLE) {
                $this->updateCount($comment, -1);
            }
        }
    }

    /**
     * Handle the Comment "deleted" event.
     */
    public function deleted(Comment $comment): void
    {
        if ($comment->status === CommentStatusEnum::VISIBLE) {
            $this->updateCount($comment, -1);
        }
    }

    /**
     * Sênior: Atualiza o contador de comentários no post de forma eficiente.
     */
    private function updateCount(Comment $comment, int $amount): void
    {
        $post = $comment->post;

        if ($post) {
            $post->timestamps = false;
            $post->increment('comments_count', $amount);
        }
    }
}
