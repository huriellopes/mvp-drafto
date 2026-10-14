<?php

declare(strict_types=1);

namespace Tests\Feature\View;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;

function renderDashboardLayoutAs(User $user): string
{
    Profile::factory()->create(['user_id' => $user->id]);
    test()->actingAs($user);

    return Blade::render('<x-app-layout>painel</x-app-layout>');
}

it('shows the Horizon and Pulse links to super admins, opening in a new tab', function () {
    $html = renderDashboardLayoutAs(User::factory()->superAdmin()->create());

    expect($html)
        ->toContain('Filas (Horizon)')
        ->toContain('href="' . url('horizon') . '"')
        ->toContain('Monitoramento (Pulse)')
        ->toContain('href="' . url('pulse') . '"')
        ->toContain('target="_blank"');
});

it('hides the operational panels from other users', function () {
    $html = renderDashboardLayoutAs(User::factory()->writer()->create());

    expect($html)
        ->not->toContain('Filas (Horizon)')
        ->not->toContain('Monitoramento (Pulse)');
});

it('lets only super admins view Pulse', function () {
    $admin = User::factory()->superAdmin()->create();
    $writer = User::factory()->writer()->create();

    expect(Gate::forUser($admin)->allows('viewPulse'))->toBeTrue()
        ->and(Gate::forUser($writer)->allows('viewPulse'))->toBeFalse()
        ->and(Gate::allows('viewPulse'))->toBeFalse();
});

it('shows the Reverb cards on the Pulse dashboard', function () {
    $dashboard = file_get_contents(resource_path('views/vendor/pulse/dashboard.blade.php'));

    expect($dashboard)
        ->toContain('<livewire:reverb.connections')
        ->toContain('<livewire:reverb.messages');
});
