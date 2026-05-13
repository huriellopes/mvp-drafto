<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Profile;

use App\Livewire\Dashboard\Profile\EditProfile;
use App\Models\Profile;
use App\Models\User;
use Livewire\Livewire;

it('renders the edit profile page', function () {
    $user = User::factory()->create();
    Profile::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('dashboard.profile'))
        ->assertOk()
        ->assertSeeLivewire(EditProfile::class);
});

it('can update profile information', function () {
    $user = User::factory()->create(['name' => 'Old Name']);
    Profile::factory()->create([
        'user_id' => $user->id,
        'username' => 'olduser',
        'bio' => 'Old bio',
    ]);

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->set('form.name', 'New Name')
        ->set('form.username', 'newuser')
        ->set('form.bio', 'New bio description')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertEquals('New Name', $user->refresh()->name);
    $this->assertEquals('newuser', $user->profile->username);
});

it('validates profile fields', function () {
    $user = User::factory()->create();
    Profile::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->set('form.name', 'abc') // min:5
        ->set('form.username', '') // required
        ->call('save')
        ->assertHasErrors(['form.name', 'form.username']);
});
