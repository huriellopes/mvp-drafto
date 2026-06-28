<?php

declare(strict_types=1);

namespace Tests\Feature\Mail;

use App\Mail\ReengagementMail;
use App\Models\User;

it('uses the early-stage invite subject for readers without new content at a low stage', function () {
    // Reader, no new published content since login -> MODE_READER_INVITE_WRITER.
    // Stage < 30 hits the default arm of the inner match (line 61).
    $reader = User::factory()->create(['last_login_at' => now()->subDays(20)]);

    $mail = new ReengagementMail($reader, 0, 20);

    $envelope = $mail->envelope();

    expect($envelope->subject)->toBe('Sentimos sua falta no Drafto 👋');
});
