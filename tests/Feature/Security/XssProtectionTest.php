<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Actions\Posts\SavePostAction;
use App\DTOs\SavePostData;
use App\Enums\PostStatusEnum;
use App\Enums\RoleEnum;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('stored xss protection: post content must be sanitized', function () {
    $user = User::factory()->create(['role' => RoleEnum::WRITER]);
    $category = PostCategory::factory()->create(['user_id' => $user->id]);

    $maliciousContent = '<script>alert("xss")</script><p>Normal content</p><img src="x" onerror="alert(1)">';

    $dto = new SavePostData(
        title: 'Malicious Post',
        slug: 'malicious-post',
        category_id: $category->id,
        content: $maliciousContent,
        status: PostStatusEnum::PUBLISHED,
    );

    $action = app(SavePostAction::class);
    $post = $action->exec($user, $dto);

    // Se o conteúdo for salvo exatamente como enviado, o PenTest falha.
    // O ideal seria que as tags <script> e atributos onerror fossem removidos ou escapados.
    expect($post->content)->not->toContain('<script>')
        ->and($post->content)->not->toContain('onerror');
});
