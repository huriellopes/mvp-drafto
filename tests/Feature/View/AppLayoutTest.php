<?php

declare(strict_types=1);

namespace Tests\Feature\View;

use App\Models\Profile;
use App\Models\User;
use App\View\Components\AppLayout;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\View;

it('points the app layout component to the layouts.app view', function () {
    $view = (new AppLayout())->render();

    expect($view)->toBeInstanceOf(View::class)
        ->and($view->name())->toBe('layouts.app');
});

it('renders the app layout with its slot for an authenticated user', function () {
    $user = User::factory()->writer()->create();
    Profile::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    $html = Blade::render(
        '<x-app-layout>Conteúdo do painel</x-app-layout>',
    );

    expect($html)->toContain('Conteúdo do painel')
        ->and($html)->toContain('<!DOCTYPE html>');
});
