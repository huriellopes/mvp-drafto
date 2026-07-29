<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Forms;

use App\Livewire\Forms\Public\ReportForm;
use App\Livewire\Public\ReportModal;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Livewire\Livewire;

it('opens the report modal and stores a valid report', function () {
    $reporter = User::factory()->active()->create();
    $post = Post::factory()->published()->create();

    Livewire::actingAs($reporter)
        ->test(ReportModal::class)
        ->call('open', 'post', $post->id)
        ->assertSet('show', true)
        ->assertSet('form.reportable_id', $post->id)
        ->assertSet('form.reportable_type', Post::class)
        ->set('form.reason', 'spam')
        ->set('form.description', 'Este conteúdo é claramente spam repetido.')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('show', false);

    expect(Report::query()
        ->where('reportable_type', Post::class)
        ->where('reportable_id', $post->id)
        ->where('reason', 'spam')
        ->exists())->toBeTrue();
});

it('validates the report description length', function () {
    $reporter = User::factory()->active()->create();
    $post = Post::factory()->published()->create();

    Livewire::actingAs($reporter)
        ->test(ReportModal::class)
        ->call('open', 'post', $post->id)
        ->set('form.reason', 'spam')
        ->set('form.description', 'curto')
        ->call('submit')
        ->assertHasErrors(['form.description']);

    expect(Report::query()->count())->toBe(0);
});

it('redirects guests to login when opening the modal', function () {
    Livewire::test(ReportModal::class)
        ->call('open', 'post', 1)
        ->assertRedirect(route('login'));
});

it('maps a friendly target type to the model class', function () {
    $form = new ReportForm(new ReportModal, 'form');
    $form->setTarget('user', 42);

    expect($form->reportable_type)->toBe(User::class)
        ->and($form->reportable_id)->toBe(42);
});

it('exposes the report payload via getData', function () {
    $reporter = User::factory()->active()->create();
    $this->actingAs($reporter);

    $form = new ReportForm(new ReportModal, 'form');
    $form->setTarget('post', 7);
    $form->reason = 'abuse';
    $form->description = '  conteúdo abusivo  ';

    $data = $form->getData();

    expect($data['reportable_type'])->toBe(Post::class)
        ->and($data['reportable_id'])->toBe(7)
        ->and($data['reason'])->toBe('abuse')
        ->and($data['description'])->toBe('conteúdo abusivo')
        ->and($data['reporter_id'])->toBe($reporter->id);
});
