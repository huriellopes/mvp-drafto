<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\UserStatusEnum;
use App\Livewire\Auth\MagicLinkRequest;
use App\Models\MagicLoginToken;
use App\Models\User;
use App\Notifications\Auth\MagicLinkNotification;
use DateTimeInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

// Isola o RateLimiter (usa o cache) entre os testes.
beforeEach(fn () => Cache::flush());

function issueMagicToken(User $user, ?string $plain = null, ?DateTimeInterface $expiresAt = null): string
{
    $plain = $plain ?? 'plain-magic-token-' . $user->id;

    $user->magicLoginTokens()->create([
        'token' => MagicLoginToken::hashToken($plain),
        'expires_at' => $expiresAt ?? MagicLoginToken::expiresAt(),
    ]);

    return $plain;
}

it('sends a magic link to an existing user and stores a hashed token', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'user@example.com']);

    Livewire::test(MagicLinkRequest::class)
        ->set('form.email', 'user@example.com')
        ->call('sendLink')
        ->assertHasNoErrors()
        ->assertSet('sent', true);

    Notification::assertSentTo($user, MagicLinkNotification::class);

    $token = $user->magicLoginTokens()->first();
    expect($token)->not->toBeNull()
        ->and($token->token)->not->toBe('') // hash armazenado, nunca texto puro
        ->and(mb_strlen($token->token))->toBe(64); // sha256 hex
});

it('does not reveal whether an email exists (neutral response)', function () {
    Notification::fake();

    Livewire::test(MagicLinkRequest::class)
        ->set('form.email', 'ghost@example.com')
        ->call('sendLink')
        ->assertHasNoErrors()
        ->assertSet('sent', true); // mesma UI de sucesso

    Notification::assertNothingSent();
    expect(MagicLoginToken::query()->count())->toBe(0);
});

it('throttles magic link requests after 3 attempts (rate limit is effective)', function () {
    Notification::fake();

    User::factory()->create(['email' => 'flood@example.com']);

    $component = Livewire::test(MagicLinkRequest::class);

    foreach (range(1, 3) as $ignored) {
        $component->set('form.email', 'flood@example.com')->call('sendLink')->assertHasNoErrors();
    }

    // 4ª tentativa dentro da janela é bloqueada pelo throttle.
    $component->set('form.email', 'flood@example.com')->call('sendLink')->assertHasErrors('form.email');
});

it('logs the user in and redirects to the dashboard with a valid token', function () {
    $user = User::factory()->create(['status' => UserStatusEnum::ACTIVE]);
    $plain = issueMagicToken($user);

    $this->get(route('auth.magic.verify', $plain))
        ->assertRedirect(route('dashboard.index'));

    $this->assertAuthenticatedAs($user);
    // uso único: o token é consumido
    expect($user->magicLoginTokens()->count())->toBe(0);
});

it('rejects an expired token without logging in', function () {
    $user = User::factory()->create();
    $plain = issueMagicToken($user, expiresAt: now()->subMinute());

    $this->get(route('auth.magic.verify', $plain))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

it('rejects an unknown token', function () {
    $this->get(route('auth.magic.verify', 'does-not-exist'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

it('does not log in directly when the user has 2FA, routing to the challenge', function () {
    $user = User::factory()->create([
        'two_factor_secret' => 'SECRETKEY',
        'two_factor_confirmed_at' => now(),
    ]);
    $plain = issueMagicToken($user);

    $this->get(route('auth.magic.verify', $plain))
        ->assertRedirect(route('auth.two-factor'));

    $this->assertGuest(); // login só após o desafio 2FA
    expect(session('auth.2fa.id'))->toBe($user->id);
});

it('builds the magic link email with the correct subject and verify url', function () {
    $user = User::factory()->create();

    $mail = (new MagicLinkNotification('the-plain-token'))->toMail($user);

    expect($mail->subject)->toBe(__('mail.auth.magic_link.subject'))
        ->and($mail->viewData['url'])->toContain('/login/magic/the-plain-token');
});

it('redirects already-authenticated users away from the magic link request page', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(MagicLinkRequest::class)
        ->assertRedirect(route('dashboard.index'));
});

it('rejects an inactive account', function () {
    $user = User::factory()->create(['status' => UserStatusEnum::INACTIVE]);
    $plain = issueMagicToken($user);

    $this->get(route('auth.magic.verify', $plain))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
