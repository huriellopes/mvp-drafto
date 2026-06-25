<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileSetting extends Model
{
    protected $fillable = [
        'profile_id',
        'button_style',
        'card_style',
        'layout_type',
        'font_family',
        'secondary_color',
        'text_color',
        'background_color',
        'show_subscriber_count',
        'show_view_count',
    ];

    protected $casts = [
        'show_subscriber_count' => 'boolean',
        'show_view_count' => 'boolean',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
