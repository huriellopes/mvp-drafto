<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Admin;

use App\Livewire\Dashboard\Admin\System\LogViewerIndex;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function insertFailedJob(string $uuid, string $job = 'App\\Jobs\\SendNewsletter'): void
{
    DB::table('failed_jobs')->insert([
        'uuid' => $uuid,
        'connection' => 'redis',
        'queue' => 'default',
        'payload' => json_encode(['displayName' => $job]),
        'exception' => "RuntimeException: boom\n#0 /app/...",
        'failed_at' => now(),
    ]);
}

it('blocks non-admin users from the system logs page', function () {
    $writer = User::factory()->writer()->create();

    $this->actingAs($writer)
        ->get(route('dashboard.admin.system-logs.index'))
        ->assertForbidden();
});

it('lets an admin open the system logs page', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard.admin.system-logs.index'))
        ->assertOk()
        ->assertSeeLivewire(LogViewerIndex::class);
});

it('shows failed jobs in the jobs tab', function () {
    $admin = User::factory()->superAdmin()->create();
    insertFailedJob('uuid-aaa');

    $this->actingAs($admin);

    Livewire::test(LogViewerIndex::class)
        ->set('tab', 'jobs')
        ->assertSee('SendNewsletter');
});

it('forgets a failed job', function () {
    $admin = User::factory()->superAdmin()->create();
    insertFailedJob('uuid-bbb');

    $this->actingAs($admin);

    Livewire::test(LogViewerIndex::class)
        ->set('tab', 'jobs')
        ->call('forgetJob', 'uuid-bbb');

    expect(DB::table('failed_jobs')->where('uuid', 'uuid-bbb')->exists())->toBeFalse();
});
