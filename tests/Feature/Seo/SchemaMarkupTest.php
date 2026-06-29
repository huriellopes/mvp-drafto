<?php

declare(strict_types=1);

namespace Tests\Feature\Seo;

use App\Enums\ModuleEnum;
use App\Models\Module;
use App\Models\Post;
use App\Models\User;
use App\Services\Post\PostSeoGenerator;
use App\Services\Profile\ProfileSeoGenerator;

/**
 * Enables the author-facing SEO module setting so the generators attach schema.
 */
function enableSeoModule(User $user, ModuleEnum $module): void
{
    $record = Module::query()->where('slug', $module->value)->firstOrFail();

    $user->modules()->syncWithoutDetaching([
        $record->id => ['is_enabled' => true, 'settings' => json_encode(['enable_seo' => true])],
    ]);
}

it('renders Article and BreadcrumbList JSON-LD for a post', function () {
    $post = Post::factory()->published()->create(['seo_enabled' => true]);
    enableSeoModule($post->author, ModuleEnum::MY_POSTS);

    $seo = PostSeoGenerator::generate($post->fresh('author'));
    $html = (string) seo($seo);

    expect($html)
        ->toContain('application/ld+json')
        ->toContain('"@type":"Article"')
        ->toContain('"@type":"BreadcrumbList"')
        ->toContain('"@type":"ListItem"');
});

it('does not render JSON-LD for a post when the SEO module is disabled', function () {
    $post = Post::factory()->published()->create(['seo_enabled' => true]);

    $html = (string) seo(PostSeoGenerator::generate($post));

    expect($html)->not->toContain('application/ld+json');
});

it('renders ProfilePage/Person JSON-LD for an indexable profile', function () {
    $user = User::factory()->writer()->withProfile()->create();
    enableSeoModule($user, ModuleEnum::PROFILE);

    $seo = ProfileSeoGenerator::generate($user->fresh()->profile);
    $html = (string) seo($seo);

    expect($html)
        ->toContain('"@type":"ProfilePage"')
        ->toContain('"@type":"Person"');
});

it('does not render Person JSON-LD when the profile is not searchable', function () {
    $user = User::factory()->writer()->withProfile()->create();
    enableSeoModule($user, ModuleEnum::PROFILE);
    $user->profile->update(['is_searchable' => false]);

    $html = (string) seo(ProfileSeoGenerator::generate($user->fresh()->profile));

    expect($html)->not->toContain('"@type":"Person"');
});

it('emits site-level Organization and WebSite SearchAction in the public head', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('"@type":"Organization"', false)
        ->assertSee('"@type":"WebSite"', false)
        ->assertSee('"@type":"SearchAction"', false)
        ->assertSee('search_term_string', false);
});
