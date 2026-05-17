<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Dashboard;

use App\Enums\CommentStatusEnum;
use App\Models\Comment;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Form;

class CommentForm extends Form
{
    public ?Comment $comment = null;

    public string $content = '';

    public string $status = '';

    public function setComment(Comment $comment): void
    {
        $this->comment = $comment;
        $this->content = $comment->content;
        $this->status = $comment->status->value;
    }

    public function rules(): array
    {
        $isAuthor = $this->comment && $this->comment->user_id === auth()->id();

        return [
            'content' => [$isAuthor ? 'required' : 'nullable', 'string', 'min:3', 'max:1000'],
            'status' => ['required', Rule::enum(CommentStatusEnum::class)],
        ];
    }

    public function update(): void
    {
        $this->validate();

        $isAuthor = $this->comment->user_id === auth()->id();
        $originalStatus = $this->comment->status;

        $data = ['status' => $this->status];

        if ($isAuthor) {
            $data['content'] = $this->content;
        }

        $this->comment->update($data);

        if ($originalStatus->value !== $this->status) {
            $authorName = $this->comment->user->name ?? 'Anônimo';
            $moderatorName = auth()->user()->name;
            $newStatusLabel = CommentStatusEnum::tryFrom($this->status)?->label() ?? 'Desconhecido';

            Log::channel('telegram_support')->info("💬 Comentário moderado por **{$moderatorName}**", [
                'author' => $authorName,
                'comment_id' => $this->comment->id,
                'new_status' => $newStatusLabel,
                'content_preview' => mb_substr($this->comment->content, 0, 100),
                'moderator' => $moderatorName,
            ]);
        }

        $this->reset();
    }
}
