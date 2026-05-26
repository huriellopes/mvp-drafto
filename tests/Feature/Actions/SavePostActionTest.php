<?php

declare(strict_types=1);

use App\Actions\Posts\SavePostAction;
use App\DTOs\SavePostData;
use App\Enums\PostStatusEnum;
use App\Enums\PostTypeEnum;
use App\Enums\PostVisibilityEnum;
use App\Enums\RoleEnum;
use App\Events\Posts\PostSaved;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->action = new SavePostAction();
});

it('creates a new draft post and dispatches media processing event', function () {
    Event::fake();

    $user = User::factory()->create(['role' => RoleEnum::SUPER_ADMIN]);
    $category = PostCategory::factory()->create(['user_id' => $user->id]);

    $dto = new SavePostData(
        title: 'Draft Post',
        slug: 'draft-post',
        category_id: $category->id,
        content: 'Draft Content',
        excerpt: 'Draft excerpt',
        tags: [],
        type: PostTypeEnum::POST,
        visibility: PostVisibilityEnum::PUBLIC,
        status: PostStatusEnum::DRAFT,
        comments_enabled: true,
        seo_enabled: true,
        seo_title: 'Seo Title',
        seo_description: 'Seo Desc',
        cover_image_path: null,
    );

    $post = $this->action->exec($user, $dto);

    expect($post->title)->toBe('Draft Post')
        ->and($post->status)->toBe(PostStatusEnum::DRAFT)
        ->and($post->user_id)->toBe($user->id)
        ->and($post->published_at)->toBeNull();

    Event::assertDispatched(PostSaved::class, function ($event) use ($post) {
        return $event->post->id === $post->id && $event->seoData['title'] === 'Seo Title';
    });
});

it('sets published_at when creating a new published post', function () {
    Event::fake();

    $user = User::factory()->create(['role' => RoleEnum::SUPER_ADMIN]);
    $category = PostCategory::factory()->create(['user_id' => $user->id]);

    $dto = new SavePostData(
        title: 'Published Post',
        slug: 'published-post',
        category_id: $category->id,
        content: 'Content',
        excerpt: 'Excerpt',
        tags: [],
        type: PostTypeEnum::POST,
        visibility: PostVisibilityEnum::PUBLIC,
        status: PostStatusEnum::PUBLISHED,
        comments_enabled: true,
        seo_enabled: true,
        seo_title: null,
        seo_description: null,
        cover_image_path: null,
    );

    $post = $this->action->exec($user, $dto);

    expect($post->status)->toBe(PostStatusEnum::PUBLISHED)
        ->and($post->published_at)->not->toBeNull();

    Event::assertDispatched(PostSaved::class);
});

it('automatically generates an excerpt when publishing if none is provided', function () {
    Event::fake();

    $user = User::factory()->create(['role' => RoleEnum::SUPER_ADMIN]);
    $category = PostCategory::factory()->create(['user_id' => $user->id]);

    $content = 'This is a long content that should be used to generate an excerpt automatically when the user decides to publish the post without providing one themselves. It should be clean and limited.';

    $dto = new SavePostData(
        title: 'Post without Excerpt',
        slug: 'post-without-excerpt',
        category_id: $category->id,
        content: $content,
        excerpt: '', // Empty excerpt
        tags: [],
        type: PostTypeEnum::POST,
        visibility: PostVisibilityEnum::PUBLIC,
        status: PostStatusEnum::PUBLISHED,
    );

    $post = $this->action->exec($user, $dto);

    expect($post->excerpt)->not->toBeEmpty()
        ->and($post->excerpt)->toBe(Str::limit(strip_tags($content), 160));
});

it('correctly saves a scheduled post with a specific published_at date', function () {
    Event::fake();

    $user = User::factory()->create(['role' => RoleEnum::SUPER_ADMIN]);
    $category = PostCategory::factory()->create(['user_id' => $user->id]);
    $scheduledDate = now()->addDays(2)->format('Y-m-d H:i');

    $dto = new SavePostData(
        title: 'Scheduled Post',
        slug: 'scheduled-post',
        category_id: $category->id,
        content: 'Content',
        status: PostStatusEnum::SCHEDULED,
        published_at: $scheduledDate,
    );

    $post = $this->action->exec($user, $dto);

    expect($post->status)->toBe(PostStatusEnum::SCHEDULED)
        ->and($post->published_at->format('Y-m-d H:i'))->toBe($scheduledDate);
});
