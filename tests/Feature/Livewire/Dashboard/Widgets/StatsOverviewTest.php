<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Widgets;

use App\Livewire\Dashboard\Widgets\StatsOverview;
use App\Models\Post;
use App\Models\User;
use Livewire\Livewire;

it('returns admin stats with five cards', function () {
    $admin = User::factory()->superAdmin()->withProfile()->create();

    $this->actingAs($admin);

    $stats = Livewire::test(StatsOverview::class)->get('stats');

    expect($stats)->toHaveCount(5)
        ->and($stats[0]['title'])->toBe('Total Usuários');
});

it('returns writer stats with their own published count', function () {
    $writer = User::factory()->writer()->withProfile()->create();
    Post::factory()->published()->create(['user_id' => $writer->id]);
    Post::factory()->draft()->create(['user_id' => $writer->id]);

    $this->actingAs($writer);

    $stats = Livewire::test(StatsOverview::class)->get('stats');

    expect($stats)->toHaveCount(5)
        ->and($stats[0]['title'])->toBe('Posts Publicados')
        ->and($stats[0]['value'])->toBe(1);
});

it('returns reader stats with four cards', function () {
    $reader = User::factory()->reader()->withProfile()->create();

    $this->actingAs($reader);

    $stats = Livewire::test(StatsOverview::class)->get('stats');

    expect($stats)->toHaveCount(4)
        ->and($stats[0]['title'])->toBe('Artigos Lidos');
});

it('renders the stats overview widget', function () {
    $user = User::factory()->writer()->withProfile()->create();

    $this->actingAs($user);

    Livewire::test(StatsOverview::class)->assertStatus(200);
});
