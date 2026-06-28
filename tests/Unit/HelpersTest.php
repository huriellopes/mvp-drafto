<?php

declare(strict_types=1);

describe('format_display_name', function () {
    it('returns "Usuário" for null, empty or whitespace-only names', function () {
        expect(format_display_name(null))->toBe('Usuário')
            ->and(format_display_name(''))->toBe('Usuário')
            ->and(format_display_name('   '))->toBe('Usuário');
    });

    it('returns the single name when there is only one word', function () {
        expect(format_display_name('Madonna'))->toBe('Madonna');
    });

    it('returns first and last name, dropping the middle names', function () {
        expect(format_display_name('John Doe'))->toBe('John Doe')
            ->and(format_display_name('John Michael Doe'))->toBe('John Doe');
    });

    it('collapses extra whitespace between names', function () {
        expect(format_display_name('  John     Doe  '))->toBe('John Doe');
    });
});

describe('get_initials', function () {
    it('returns the "DR" fallback for null or empty names', function () {
        expect(get_initials(null))->toBe('DR')
            ->and(get_initials(''))->toBe('DR');
    });

    it('returns up to two uppercase initials', function () {
        expect(get_initials('John Doe'))->toBe('JD')
            ->and(get_initials('john michael doe'))->toBe('JM')
            ->and(get_initials('madonna'))->toBe('M');
    });
});

describe('format_currency', function () {
    it('formats values as Brazilian Real', function () {
        expect(format_currency(1234.5))->toBe('R$ 1.234,50')
            ->and(format_currency(0))->toBe('R$ 0,00')
            ->and(format_currency(1000000))->toBe('R$ 1.000.000,00');
    });
});
