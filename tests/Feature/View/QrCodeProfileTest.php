<?php

declare(strict_types=1);

namespace Tests\Feature\View;

use App\View\Components\UI\QrCodeProfile;
use Illuminate\Support\Facades\Blade;

it('generates an inline svg qr code from the given data', function () {
    $component = new QrCodeProfile(data: 'https://drafto.test/u/john');

    expect($component->qrCodeData)->toContain('<svg')
        ->and($component->size)->toBe(200)
        ->and($component->color)->toBe('#000000');
});

it('honors a custom size and color', function () {
    $component = new QrCodeProfile(
        data: 'https://drafto.test/u/jane',
        size: 320,
        color: '#ff0000',
    );

    expect($component->size)->toBe(320)
        ->and($component->color)->toBe('#ff0000');
});

it('renders the blade component without throwing', function () {
    $html = Blade::render(
        '<x-u-i.qr-code-profile data="https://drafto.test/u/john" :size="150" />',
    );

    expect($html)->toContain('qrcode-wrapper')
        ->and($html)->toContain('<svg')
        ->and($html)->toContain('150px');
});
