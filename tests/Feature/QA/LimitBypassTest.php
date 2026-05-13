<?php

declare(strict_types=1);

use App\Actions\Posts\SavePostAction;
use App\DTOs\SavePostData;
use App\Enums\ModuleEnum;
use App\Enums\PlanEnum;
use App\Enums\PostStatusEnum;
use App\Enums\RoleEnum;
use App\Models\Module;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;

it('reproduces the limit bypass: can publish a draft even when limit is reached', function () {
    // 1. Setup a FREE user with a limit of 1 post
    $user = User::factory()->create(['role' => RoleEnum::WRITER]);

    // Ensure the module settings are loaded
    $module = Module::updateOrCreate(
        ['slug' => ModuleEnum::MY_POSTS->value],
        ['name' => 'My Posts', 'settings' => ['max_posts' => [PlanEnum::FREE->value => 1]]],
    );

    $category = PostCategory::factory()->create(['user_id' => $user->id]);

    // 2. Create one published post to reach the limit
    Post::factory()->create([
        'user_id' => $user->id,
        'status' => PostStatusEnum::PUBLISHED,
        'published_at' => now(),
    ]);

    expect($user->hasReachedPostLimit())->toBeTrue();

    // 3. Create a DRAFT (should work as there is no draft limit check in this action for drafts)
    $action = new SavePostAction();
    $draftDto = new SavePostData(
        title: 'Draft Post',
        slug: 'draft-post',
        category_id: $category->id,
        content: 'Content',
        status: PostStatusEnum::DRAFT,
    );
    $post = $action->exec($user, $draftDto);

    expect($post->status)->toBe(PostStatusEnum::DRAFT);

    // 4. ATTEMPT TO PUBLISH THE DRAFT
    // This SHOULD now fail because we fixed the logic in SavePostAction.
    $publishDto = new SavePostData(
        title: 'Published Draft',
        slug: 'draft-post',
        category_id: $category->id,
        content: 'Content',
        status: PostStatusEnum::PUBLISHED,
    );

    // Expecting the exception defined in SavePostAction
    expect(fn () => $action->exec($user, $publishDto, $post))
        ->toThrow(Exception::class, 'Você atingiu o limite de publicações mensais do seu plano.');

    // Verify the post remains a draft
    expect($post->refresh()->status)->toBe(PostStatusEnum::DRAFT);
});
