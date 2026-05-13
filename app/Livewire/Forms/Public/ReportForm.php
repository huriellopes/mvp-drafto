<?php

declare(strict_types=1);

namespace App\Livewire\Forms\Public;

use App\Enums\ReportReasonEnum;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Validation\Rules\Enum;
use Livewire\Form;

class ReportForm extends Form
{
    public ?string $reportable_type = null;

    public ?int $reportable_id = null;

    public string $reason = 'other';

    public string $description = '';

    public function rules(): array
    {
        return [
            'reason' => ['required', new Enum(ReportReasonEnum::class)],
            'description' => 'required|string|min:10|max:1000',
            'reportable_type' => 'required|string',
            'reportable_id' => 'required|integer',
        ];
    }

    public function setTarget(string $type, $id): void
    {
        $map = [
            'user' => User::class,
            'post' => Post::class,
            'comment' => Comment::class,
        ];

        $this->reportable_type = $map[mb_strtolower($type)] ?? $type;
        $this->reportable_id = (int) $id;
    }

    public function getData(): array
    {
        return [
            'reportable_type' => $this->reportable_type,
            'reportable_id' => $this->reportable_id,
            'reason' => $this->reason,
            'description' => mb_trim((string) $this->description),
            'reporter_id' => auth()->id(),
        ];
    }
}
