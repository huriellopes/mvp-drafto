<?php

declare(strict_types=1);

namespace Tests\Feature\Mail;

it('exposes a scheme key on the smtp, contact and support mailers', function () {
    expect(config('mail.mailers.smtp'))->toHaveKey('scheme')
        ->and(config('mail.mailers.contact'))->toHaveKey('scheme')
        ->and(config('mail.mailers.support'))->toHaveKey('scheme');
});

it('resolves MAIL_SCHEME=smtps for all SMTP mailers (port 465 / TLS implícito)', function () {
    // Simula o ambiente de produção (porta 465 exige scheme smtps).
    putenv('MAIL_SCHEME=smtps');
    $_ENV['MAIL_SCHEME'] = 'smtps';
    $_SERVER['MAIL_SCHEME'] = 'smtps';

    // Reavalia o arquivo de config com a env definida.
    $mail = require base_path('config/mail.php');

    expect($mail['mailers']['smtp']['scheme'])->toBe('smtps')
        ->and($mail['mailers']['contact']['scheme'])->toBe('smtps')
        ->and($mail['mailers']['support']['scheme'])->toBe('smtps');

    putenv('MAIL_SCHEME');
    unset($_ENV['MAIL_SCHEME'], $_SERVER['MAIL_SCHEME']);
});

it('lets each mailer override the scheme individually when needed', function () {
    putenv('MAIL_CONTACT_SCHEME=smtps');
    $_ENV['MAIL_CONTACT_SCHEME'] = 'smtps';
    $_SERVER['MAIL_CONTACT_SCHEME'] = 'smtps';

    $mail = require base_path('config/mail.php');

    expect($mail['mailers']['contact']['scheme'])->toBe('smtps');

    putenv('MAIL_CONTACT_SCHEME');
    unset($_ENV['MAIL_CONTACT_SCHEME'], $_SERVER['MAIL_CONTACT_SCHEME']);
});
