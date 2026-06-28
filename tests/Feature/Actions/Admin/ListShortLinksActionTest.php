<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Admin;

use App\Actions\Admin\ListShortLinksAction;
use App\DTOs\ShortLinkFilterData;
use App\Models\Post;
use App\Models\ShortLink;
use App\Models\User;

beforeEach(function () {
    $this->action = app(ListShortLinksAction::class);
});

function makeShortLink(array $attributes = []): ShortLink
{
    $user = User::factory()->withProfile()->create();
    $post = Post::factory()->published()->create(['user_id' => $user->id]);

    return ShortLink::factory()->create(array_merge([
        'user_id' => $user->id,
        'shortable_type' => $post->getMorphClass(),
        'shortable_id' => $post->id,
    ], $attributes));
}

it('lists short links with eager loaded relations', function () {
    makeShortLink();
    makeShortLink();

    $result = $this->action->exec(new ShortLinkFilterData());

    expect($result->total())->toBe(2)
        ->and($result->first()->relationLoaded('user'))->toBeTrue();
});

it('filters by code', function () {
    $target = makeShortLink(['code' => 'ABC123']);
    makeShortLink(['code' => 'ZZZ999']);

    $result = $this->action->exec(new ShortLinkFilterData(search: 'ABC123'));

    expect($result->total())->toBe(1)
        ->and($result->first()->id)->toBe($target->id);
});

it('filters by owning user name', function () {
    $user = User::factory()->withProfile()->create(['name' => 'Findable Author']);
    $post = Post::factory()->published()->create(['user_id' => $user->id]);
    ShortLink::factory()->create([
        'user_id' => $user->id,
        'code' => 'XYZ000',
        'shortable_type' => $post->getMorphClass(),
        'shortable_id' => $post->id,
    ]);
    makeShortLink();

    $result = $this->action->exec(new ShortLinkFilterData(search: 'Findable Author'));

    expect($result->total())->toBe(1)
        ->and($result->first()->user_id)->toBe($user->id);
});
