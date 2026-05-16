<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Dashboard;

use App\Enums\CommentStatusEnum;
use App\Models\Comment;
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

        $data = ['status' => $this->status];

        if ($isAuthor) {
            $data['content'] = $this->content;
        }

        $this->comment->update($data);

        $this->reset();
    }
}
