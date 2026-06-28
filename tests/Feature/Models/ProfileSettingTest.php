<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Profile;
use App\Models\ProfileSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

it('is fillable and casts boolean flags', function () {
    $user = User::factory()->create();
    $profile = Profile::factory()->create(['user_id' => $user->id]);

    $setting = ProfileSetting::create([
        'profile_id' => $profile->id,
        'button_style' => 'rounded-md',
        'card_style' => 'bordered',
        'layout_type' => 'default',
        'font_family' => 'sans',
        'show_subscriber_count' => 1,
        'show_view_count' => 0,
    ]);

    expect($setting->button_style)->toBe('rounded-md')
        ->and($setting->show_subscriber_count)->toBeTrue()
        ->and($setting->show_view_count)->toBeFalse();
});

it('belongs to a profile', function () {
    $user = User::factory()->create();
    $profile = Profile::factory()->create(['user_id' => $user->id]);
    $setting = ProfileSetting::create([
        'profile_id' => $profile->id,
        'button_style' => 'rounded-md',
    ]);

    expect($setting->profile())->toBeInstanceOf(BelongsTo::class)
        ->and($setting->profile->id)->toBe($profile->id);
});
