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
        ->set('form.email', 'newuser@example.com')
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
        ->set('form.name', 'ab') // min:3
        ->set('form.username', '') // required
        ->call('save')
        ->assertHasErrors(['form.name', 'form.username']);
});

it('prepends https:// to a website url typed without a scheme', function () {
    $user = User::factory()->create();
    Profile::factory()->create(['user_id' => $user->id, 'username' => 'siteuser']);

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->set('form.name', 'Site User')
        ->set('form.username', 'siteuser')
        ->set('form.email', 'site@example.com')
        ->set('form.website_url', 'meusite.com')
        ->call('save')
        ->assertHasNoErrors();

    expect($user->refresh()->profile->website_url)->toBe('https://meusite.com');
});

it('fixes a duplicated scheme pasted into the website url', function () {
    $user = User::factory()->create();
    Profile::factory()->create(['user_id' => $user->id, 'username' => 'siteuser']);

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->set('form.name', 'Site User')
        ->set('form.username', 'siteuser')
        ->set('form.email', 'site@example.com')
        ->set('form.website_url', 'https://https://meusite.com')
        ->call('save')
        ->assertHasNoErrors();

    expect($user->refresh()->profile->website_url)->toBe('https://meusite.com');
});

it('clears the website url when only the scheme prefix is provided', function () {
    $user = User::factory()->create();
    Profile::factory()->create(['user_id' => $user->id, 'username' => 'siteuser', 'website_url' => 'https://old.com']);

    $this->actingAs($user);

    Livewire::test(EditProfile::class)
        ->set('form.name', 'Site User')
        ->set('form.username', 'siteuser')
        ->set('form.email', 'site@example.com')
        ->set('form.website_url', 'https://')
        ->call('save')
        ->assertHasNoErrors();

    expect($user->refresh()->profile->website_url)->toBeNull();
});
