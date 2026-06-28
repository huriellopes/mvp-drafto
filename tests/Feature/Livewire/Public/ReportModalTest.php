<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Public;

use App\Enums\ReportReasonEnum;
use App\Livewire\Public\ReportModal;
use App\Models\Post;
use App\Models\Profile;
use App\Models\Report;
use App\Models\User;
use Livewire\Livewire;

it('redirects guests to login when opening the modal', function () {
    Livewire::test(ReportModal::class)
        ->call('open', Post::class, 1)
        ->assertRedirect(route('login'))
        ->assertSet('show', false);
});

it('opens the modal for an authenticated user with a valid type', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();

    Livewire::actingAs($user)
        ->test(ReportModal::class)
        ->call('open', Post::class, $post->id)
        ->assertSet('show', true)
        ->assertSet('form.reportable_type', Post::class)
        ->assertSet('form.reportable_id', $post->id);
});

it('ignores unsupported reportable types', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ReportModal::class)
        ->call('open', Profile::class, 1)
        ->assertSet('show', false);
});

it('validates the report form on submit', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();

    Livewire::actingAs($user)
        ->test(ReportModal::class)
        ->call('open', Post::class, $post->id)
        ->set('form.description', 'short')
        ->call('submit')
        ->assertHasErrors(['form.description']);
});

it('stores a report and resets the form on success', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();

    Livewire::actingAs($user)
        ->test(ReportModal::class)
        ->call('open', Post::class, $post->id)
        ->set('form.reason', ReportReasonEnum::SPAM->value)
        ->set('form.description', 'This post is clearly spam and violates the rules.')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('show', false);

    expect(Report::query()
        ->where('reportable_type', Post::class)
        ->where('reportable_id', $post->id)
        ->where('reason', ReportReasonEnum::SPAM->value)
        ->exists())->toBeTrue();
});
