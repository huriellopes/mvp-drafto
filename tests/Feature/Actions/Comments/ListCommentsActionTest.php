<?php

declare(strict_types=1);

use App\Actions\Comments\ListCommentsAction;
use App\DTOs\CommentFilterData;
use App\Enums\CommentStatusEnum;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

beforeEach(function () {
    $this->action = app(ListCommentsAction::class);
});

it('returns only the comments belonging to a non-admin user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $own = Comment::factory()->byUser($user)->create();
    Comment::factory()->byUser($other)->create();

    $result = $this->action->exec($user, new CommentFilterData);

    expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($result->total())->toBe(1)
        ->and($result->items()[0]->id)->toBe($own->id);
});

it('includes replies made to a non-admin users comments', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $parent = Comment::factory()->byUser($user)->create();
    $reply = Comment::factory()->byUser($other)->replyTo($parent)->create();

    $result = $this->action->exec($user, new CommentFilterData);

    $ids = collect($result->items())->pluck('id');

    expect($ids)->toContain($parent->id)
        ->and($ids)->toContain($reply->id);
});

it('returns every comment for an admin user', function () {
    $admin = User::factory()->superAdmin()->create();
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    Comment::factory()->byUser($userA)->create();
    Comment::factory()->byUser($userB)->create();

    $result = $this->action->exec($admin, new CommentFilterData);

    expect($result->total())->toBe(2);
});

it('filters comments by status', function () {
    $admin = User::factory()->superAdmin()->create();

    $visible = Comment::factory()->visible()->create();
    Comment::factory()->hidden()->create();

    $result = $this->action->exec(
        $admin,
        new CommentFilterData(status: CommentStatusEnum::VISIBLE->value),
    );

    expect($result->total())->toBe(1)
        ->and($result->items()[0]->id)->toBe($visible->id);
});

it('filters comments by search on the content', function () {
    $admin = User::factory()->superAdmin()->create();

    $match = Comment::factory()->create(['content' => 'A uniquely searchable phrase here']);
    Comment::factory()->create(['content' => 'Something else entirely']);

    $result = $this->action->exec(
        $admin,
        new CommentFilterData(search: 'uniquely searchable'),
    );

    $ids = collect($result->items())->pluck('id');

    expect($ids)->toContain($match->id)
        ->and($result->total())->toBe(1);
});
