<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UpdateAudienceEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

#[Fillable([
    'title',
    'content',
    'audience',
    'created_by',
    'sent_at',
    'recipients_count',
])]
class PlatformUpdate extends Model
{
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isSent(): bool
    {
        return $this->sent_at !== null;
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'audience' => UpdateAudienceEnum::class,
            'sent_at' => 'datetime',
            'recipients_count' => 'integer',
        ];
    }
}
