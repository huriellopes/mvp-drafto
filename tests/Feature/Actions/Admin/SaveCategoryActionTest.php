<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Admin;

use App\Actions\Admin\SaveCategoryAction;
use App\DTOs\SaveCategoryData;
use App\Models\PostCategory;
use App\Models\User;

beforeEach(function () {
    $this->action = app(SaveCategoryAction::class);
});

it('creates a new category', function () {
    $user = User::factory()->create();

    $category = $this->action->exec(new SaveCategoryData(
        user_id: $user->id,
        name: 'Technology',
        slug: 'technology',
        description: 'Tech posts',
    ));

    expect($category)->toBeInstanceOf(PostCategory::class)
        ->and($category->name)->toBe('Technology')
        ->and($category->slug)->toBe('technology')
        ->and($category->user_id)->toBe($user->id);

    $this->assertDatabaseHas('post_categories', ['slug' => 'technology']);
});

it('derives a slug from the name when none is provided', function () {
    $category = $this->action->exec(new SaveCategoryData(
        user_id: null,
        name: 'My New Category',
        slug: '',
        description: null,
    ));

    expect($category->slug)->toBe('my-new-category');
});

it('updates an existing category in place', function () {
    $existing = PostCategory::factory()->create(['name' => 'Old', 'slug' => 'old']);

    $updated = $this->action->exec(
        new SaveCategoryData(
            user_id: null,
            name: 'Updated',
            slug: 'updated',
            description: 'desc',
        ),
        $existing,
    );

    expect($updated->id)->toBe($existing->id)
        ->and($updated->name)->toBe('Updated')
        ->and($updated->slug)->toBe('updated');
});
