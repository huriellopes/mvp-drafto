<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Posts;

use App\Actions\Posts\SavePostAction;
use App\DTOs\SavePostData;
use App\Enums\PostStatusEnum;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Tag;
use App\Models\User;
use Exception;

beforeEach(function () {
    $this->action = app(SavePostAction::class);
});

it('creates a category from a non-numeric string name', function () {
    $user = User::factory()->writer()->create();

    $dto = SavePostData::from([
        'title' => 'Minha obra',
        'slug' => 'minha-obra-' . uniqid(),
        'category_id' => 'Filosofia Moderna',
        'content' => '<p>Conteúdo extenso</p>',
        'status' => PostStatusEnum::DRAFT,
    ]);

    $post = $this->action->exec($user, $dto);

    $category = PostCategory::where('slug', 'filosofia-moderna')->first();

    expect($category)->not->toBeNull()
        ->and($category->user_id)->toBe($user->id)
        ->and($post->category_id)->toBe($category->id);
});

it('throws when selecting a numeric category owned by another user', function () {
    $owner = User::factory()->writer()->create();
    $other = User::factory()->writer()->create();

    $category = PostCategory::factory()->create(['user_id' => $other->id]);

    $dto = SavePostData::from([
        'title' => 'Tentativa',
        'slug' => 'tentativa-' . uniqid(),
        'category_id' => $category->id,
        'content' => '<p>algo</p>',
        'status' => PostStatusEnum::DRAFT,
    ]);

    expect(fn () => $this->action->exec($owner, $dto))
        ->toThrow(Exception::class, 'A categoria selecionada é inválida.');
});

it('auto-generates an excerpt when publishing with an empty excerpt', function () {
    $user = User::factory()->writer()->create();

    $dto = SavePostData::from([
        'title' => 'Publicação',
        'slug' => 'publicacao-' . uniqid(),
        'category_id' => null,
        'content' => '<p>' . str_repeat('palavra ', 80) . '</p>',
        'excerpt' => '   ',
        'status' => PostStatusEnum::PUBLISHED,
    ]);

    $post = $this->action->exec($user, $dto);

    expect($post->excerpt)->not->toBeNull()
        ->and(mb_strlen((string) $post->excerpt))->toBeLessThanOrEqual(164)
        ->and($post->published_at)->not->toBeNull();
});

it('sets published_at to the provided date when publishing', function () {
    $user = User::factory()->writer()->create();
    $when = now()->subDay()->startOfSecond();

    $dto = SavePostData::from([
        'title' => 'Com data',
        'slug' => 'com-data-' . uniqid(),
        'category_id' => null,
        'content' => '<p>texto</p>',
        'excerpt' => 'resumo manual',
        'status' => PostStatusEnum::PUBLISHED,
        'published_at' => $when->toDateTimeString(),
    ]);

    $post = $this->action->exec($user, $dto);

    expect($post->published_at->toDateTimeString())->toBe($when->toDateTimeString());
});

it('schedules a post and stores the scheduled published_at', function () {
    $user = User::factory()->writer()->create();
    $when = now()->addWeek()->startOfSecond();

    $dto = SavePostData::from([
        'title' => 'Agendado',
        'slug' => 'agendado-' . uniqid(),
        'category_id' => null,
        'content' => '<p>conteúdo agendado</p>',
        'status' => PostStatusEnum::SCHEDULED,
        'published_at' => $when->toDateTimeString(),
    ]);

    $post = $this->action->exec($user, $dto);

    expect($post->status)->toBe(PostStatusEnum::SCHEDULED)
        ->and($post->published_at->toDateTimeString())->toBe($when->toDateTimeString())
        ->and($post->excerpt)->not->toBeNull();
});

it('processes numeric and string tags, creating only the new ones', function () {
    $user = User::factory()->writer()->create();
    $existing = Tag::factory()->create();

    $dto = SavePostData::from([
        'title' => 'Com tags',
        'slug' => 'com-tags-' . uniqid(),
        'category_id' => null,
        'content' => '<p>texto</p>',
        'status' => PostStatusEnum::DRAFT,
        'tags' => [$existing->id, 'Nova Tag Criada'],
    ]);

    $post = $this->action->exec($user, $dto);

    $created = Tag::where('slug', 'nova-tag-criada')->first();

    expect($created)->not->toBeNull()
        ->and($created->user_id)->toBe($user->id)
        ->and($post->tags->pluck('id')->all())->toContain($existing->id, $created->id);
});

it('updates an existing post and keeps a manual excerpt sanitized', function () {
    $user = User::factory()->writer()->create();
    $post = Post::factory()->create(['user_id' => $user->id, 'status' => PostStatusEnum::DRAFT]);

    $dto = SavePostData::from([
        'title' => 'Atualizado',
        'slug' => $post->slug,
        'category_id' => null,
        'content' => '<p>novo conteúdo</p>',
        'excerpt' => '<b>resumo</b>',
        'status' => PostStatusEnum::DRAFT,
    ]);

    $updated = $this->action->exec($user, $dto, $post);

    expect($updated->id)->toBe($post->id)
        ->and($updated->title)->toBe('Atualizado');
});
