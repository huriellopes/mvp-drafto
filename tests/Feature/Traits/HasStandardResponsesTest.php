<?php

declare(strict_types=1);

namespace Tests\Feature\Traits;

use App\Traits\Livewire\HasStandardResponses;
use Livewire\Component;
use Livewire\Livewire;
use Masmerise\Toaster\Toaster;

/**
 * HasStandardResponses has no dedicated host component, so we drive it through a
 * tiny anonymous Livewire host that exposes its protected helpers.
 */
function standardResponsesHost(): Component
{
    return new class() extends Component
    {
        use HasStandardResponses;

        public function fireSuccess(): void
        {
            $this->notifySuccess('done ok');
        }

        public function fireError(): void
        {
            $this->notifyError('went wrong');
        }

        public function fireWarning(): void
        {
            $this->notifyWarning('careful now');
        }

        public function fireInfo(): void
        {
            $this->notifyInfo('just so you know');
        }

        public function render()
        {
            return '<div></div>';
        }
    };
}

it('dispatches a success toast', function () {
    Toaster::fake();

    Livewire::test(standardResponsesHost()::class)
        ->call('fireSuccess')
        ->assertHasNoErrors();

    Toaster::assertDispatched('done ok');
});

it('dispatches an error toast', function () {
    Toaster::fake();

    Livewire::test(standardResponsesHost()::class)
        ->call('fireError')
        ->assertHasNoErrors();

    Toaster::assertDispatched('went wrong');
});

it('dispatches a warning toast', function () {
    Toaster::fake();

    Livewire::test(standardResponsesHost()::class)
        ->call('fireWarning')
        ->assertHasNoErrors();

    Toaster::assertDispatched('careful now');
});

it('dispatches an info toast', function () {
    Toaster::fake();

    Livewire::test(standardResponsesHost()::class)
        ->call('fireInfo')
        ->assertHasNoErrors();

    Toaster::assertDispatched('just so you know');
});
