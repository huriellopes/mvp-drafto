<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Dashboard;

use App\Models\Comment;
use Livewire\Form;
use Illuminate\Validation\Rule;
use App\Enums\CommentStatusEnum;

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
        return [
            'content' => ['required', 'string', 'min:3', 'max:1000'],
            'status' => ['required', Rule::enum(CommentStatusEnum::class)],
        ];
    }

    public function update(): void
    {
        $this->validate();

        $this->comment->update([
            'content' => $this->content,
            'status' => $this->status,
        ]);

        $this->reset();
    }
}
