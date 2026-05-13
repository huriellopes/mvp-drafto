<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\DeletedModels\Models\Concerns\KeepsDeletedModels;

#[Fillable([
    'email',
    'verified_at',
    'verification_token',
    'receive_platform_updates',
])]
class NewsletterSubscriber extends Model implements Auditable
{
    use HasFactory, KeepsDeletedModels, \OwenIt\Auditing\Auditable;

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(PostCategory::class, 'category_newsletter_subscriber');
    }

    public function isVerified(): bool
    {
        return !is_null($this->verified_at);
    }

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'receive_platform_updates' => 'boolean',
        ];
    }
}
