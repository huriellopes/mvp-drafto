<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Modules;

use App\Actions\Modules\ResolveShortLinkAction;
use App\Models\Comment;
use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
    $this->action = app(ResolveShortLinkAction::class);
});

it('returns the home url for a shortable that is neither a user nor a post', function () {
    $comment = Comment::factory()->create();

    $shortLink = ShortLink::create([
        'user_id' => User::factory()->create()->id,
        'shortable_type' => $comment->getMorphClass(),
        'shortable_id' => $comment->id,
        'code' => 'cmt123',
    ]);

    $url = $this->action->exec($shortLink->code);

    expect($url)->toBe(url('/'));
});
