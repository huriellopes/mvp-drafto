<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Concerns;

use App\Models\Comment;
use App\Models\Report;
use App\Models\ShortLink;
use App\Models\User;

it('exposes the likedComments relationship', function () {
    $user = User::factory()->create();
    $comment = Comment::factory()->create();

    $user->likedComments()->attach($comment->id);

    expect($user->likedComments()->count())->toBe(1)
        ->and($user->likedComments->pluck('id')->all())->toContain($comment->id);
});

it('exposes the reviewedReports relationship', function () {
    $reviewer = User::factory()->superAdmin()->create();
    $report = Report::factory()->create(['reviewed_by' => $reviewer->id]);

    expect($reviewer->reviewedReports()->count())->toBe(1)
        ->and($reviewer->reviewedReports->pluck('id')->all())->toContain($report->id);
});

it('exposes the user shortLinks morph relationship', function () {
    $user = User::factory()->withProfile()->create();

    $shortLink = ShortLink::create([
        'user_id' => $user->id,
        'shortable_type' => $user->getMorphClass(),
        'shortable_id' => $user->id,
        'code' => 'usrlnk',
    ]);

    expect($user->shortLinks()->count())->toBe(1)
        ->and($user->shortLinks->pluck('id')->all())->toContain($shortLink->id);
});
