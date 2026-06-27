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
use Illuminate\Support\Facades\File;
use Mews\Purifier\Facades\Purifier;

beforeEach(function () {
    $this->action = new SavePostAction();
});

/**
 * Captura os warnings de "Style attribute ... is not supported" emitidos pelo
 * HTMLPurifier durante a execução de $callback. O cache de definições é limpo
 * antes para forçar o rebuild (os warnings só são emitidos no cache miss).
 *
 * @return array<int, string>
 */
function captureStyleWarnings(Closure $callback): array
{
    File::cleanDirectory(config('purifier.cachePath'));

    $warnings = [];
    set_error_handler(function (int $severity, string $message) use (&$warnings): bool {
        if (str_contains($message, 'Style attribute') && str_contains($message, 'is not supported')) {
            $warnings[] = $message;
        }

        return true; // suprime o handler padrão durante o bloco
    }, E_USER_WARNING);

    try {
        $callback();
    } finally {
        restore_error_handler();
    }

    return $warnings;
}

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

it('does not emit "not supported" warnings for any allowed CSS property of the post_content profile', function () {
    // setupConfigStuff() do HTMLPurifier percorre TODAS as propriedades de
    // CSS.AllowedProperties e emite um warning para cada uma sem definição
    // interna — independente do HTML de entrada. Logo, um único clean() em
    // cache miss revela qualquer propriedade mal configurada no perfil.
    $warnings = captureStyleWarnings(function () {
        Purifier::clean('<p style="color:red">x</p>', 'post_content');
    });

    expect($warnings)->toBe([], 'Propriedade(s) CSS sem suporte no perfil post_content: ' . implode(', ', $warnings));
});

it('preserves border-radius styles when saving a draft without raising warnings', function () {
    Event::fake();

    $user = User::factory()->create(['role' => RoleEnum::SUPER_ADMIN]);
    $category = PostCategory::factory()->create(['user_id' => $user->id]);

    $content = '<p style="border-radius:8px;aspect-ratio:16/9;width:100%;max-width:600px;">Conteúdo estilizado</p>';

    $post = null;
    $warnings = captureStyleWarnings(function () use ($user, $category, $content, &$post) {
        $dto = new SavePostData(
            title: 'Rascunho com estilo',
            slug: 'rascunho-com-estilo',
            category_id: $category->id,
            content: $content,
            status: PostStatusEnum::DRAFT,
        );

        $post = $this->action->exec($user, $dto);
    });

    expect($warnings)->toBe([])
        ->and($post->status)->toBe(PostStatusEnum::DRAFT)
        ->and($post->content)->toContain('border-radius')
        ->and($post->content)->toContain('aspect-ratio');
});

it('preserves border-radius styles when publishing without raising warnings', function () {
    Event::fake();

    $user = User::factory()->create(['role' => RoleEnum::SUPER_ADMIN]);
    $category = PostCategory::factory()->create(['user_id' => $user->id]);

    $content = '<p style="border-radius:12px;">Conteúdo publicado</p>';

    $post = null;
    $warnings = captureStyleWarnings(function () use ($user, $category, $content, &$post) {
        $dto = new SavePostData(
            title: 'Publicação com estilo',
            slug: 'publicacao-com-estilo',
            category_id: $category->id,
            content: $content,
            status: PostStatusEnum::PUBLISHED,
        );

        $post = $this->action->exec($user, $dto);
    });

    expect($warnings)->toBe([])
        ->and($post->status)->toBe(PostStatusEnum::PUBLISHED)
        ->and($post->content)->toContain('border-radius');
});
