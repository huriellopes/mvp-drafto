<?php

declare(strict_types=1);

use App\Actions\Engagement\SendProductUpdateAction;
use App\Mail\ProductUpdateMail;
use App\Models\PlatformUpdate;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
});

function makeUpdate(string $audience): PlatformUpdate
{
    return PlatformUpdate::create([
        'title' => 'Novidade',
        'content' => 'Conteúdo do comunicado de novidades.',
        'audience' => $audience,
    ]);
}

it('sends to writers only when audience is writers', function () {
    $writer = User::factory()->writer()->create();
    $reader = User::factory()->create();

    $count = app(SendProductUpdateAction::class)->exec(makeUpdate('writers'));

    expect($count)->toBe(1);
    Mail::assertQueued(ProductUpdateMail::class, fn ($mail) => $mail->hasTo($writer->email));
    Mail::assertNotQueued(ProductUpdateMail::class, fn ($mail) => $mail->hasTo($reader->email));
});

it('sends to readers only when audience is readers', function () {
    $writer = User::factory()->writer()->create();
    $reader = User::factory()->create();

    $count = app(SendProductUpdateAction::class)->exec(makeUpdate('readers'));

    expect($count)->toBe(1);
    Mail::assertQueued(ProductUpdateMail::class, fn ($mail) => $mail->hasTo($reader->email));
    Mail::assertNotQueued(ProductUpdateMail::class, fn ($mail) => $mail->hasTo($writer->email));
});

it('sends to everyone eligible when audience is all', function () {
    User::factory()->writer()->create();
    User::factory()->create();

    $count = app(SendProductUpdateAction::class)->exec(makeUpdate('all'));

    expect($count)->toBe(2);
    Mail::assertQueued(ProductUpdateMail::class, 2);
});
