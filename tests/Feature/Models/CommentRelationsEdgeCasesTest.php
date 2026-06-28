<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Comment;
use App\Models\Report;

it('exposes the comment reports morph relationship', function () {
    $comment = Comment::factory()->create();
    $report = Report::factory()->create([
        'reportable_type' => $comment->getMorphClass(),
        'reportable_id' => $comment->id,
    ]);

    expect($comment->reports()->count())->toBe(1)
        ->and($comment->reports->pluck('id')->all())->toContain($report->id);
});
