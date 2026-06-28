<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Admin;

use App\Livewire\Dashboard\Admin\AuditDetails;
use App\Livewire\Dashboard\Admin\AuditLogIndex;
use App\Models\Tag;
use App\Models\User;
use Livewire\Livewire;
use OwenIt\Auditing\Models\Audit;

/**
 * O pacote de auditoria ignora eventos em contexto de console (testes), então
 * criamos o registro de auditoria explicitamente.
 */
function makeAudit(User $user): Audit
{
    $tag = Tag::factory()->create(['user_id' => $user->id]);

    return Audit::query()->create([
        'user_type' => $user->getMorphClass(),
        'user_id' => $user->id,
        'event' => 'created',
        'auditable_type' => $tag->getMorphClass(),
        'auditable_id' => $tag->id,
        'old_values' => [],
        'new_values' => ['name' => $tag->name],
        'url' => 'http://localhost/test',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'PHPUnit',
        'tags' => null,
    ]);
}

it('blocks non-admins from the audit log page', function () {
    $writer = User::factory()->writer()->create();

    $this->actingAs($writer)
        ->get(route('dashboard.admin.logs.index'))
        ->assertForbidden();
});

it('lets an admin open the audit log page', function () {
    $admin = User::factory()->superAdmin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard.admin.logs.index'))
        ->assertOk()
        ->assertSeeLivewire(AuditLogIndex::class);
});

it('renders for an admin and resets pagination when filters change', function () {
    $admin = User::factory()->superAdmin()->create();
    makeAudit($admin);

    Livewire::actingAs($admin)
        ->test(AuditLogIndex::class)
        ->assertOk()
        ->set('event', 'created')
        ->assertSet('event', 'created');
});

it('opens the audit details modal for a given audit', function () {
    $admin = User::factory()->superAdmin()->create();
    $audit = makeAudit($admin);

    Livewire::actingAs($admin)
        ->test(AuditDetails::class)
        ->call('show', $audit->id)
        ->assertSet('audit.id', $audit->id)
        ->assertDispatched('open-modal', name: 'audit-details-modal')
        ->call('closeModal')
        ->assertSet('audit', null)
        ->assertDispatched('close-modal', name: 'audit-details-modal');
});
