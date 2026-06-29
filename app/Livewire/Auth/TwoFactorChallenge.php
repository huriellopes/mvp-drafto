<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Actions\Auth\VerifyTwoFactorCodeAction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

#[Layout('layouts.auth')]
class TwoFactorChallenge extends Component
{
    public string $code = '';

    public bool $recovery = false;

    public function mount(): void
    {
        if (!session()->has('auth.2fa.id')) {
            $this->redirectRoute('login');
        }
    }

    public function verify(VerifyTwoFactorCodeAction $verifyAction): void
    {
        $userId = session('auth.2fa.id');
        $user = User::findOrFail($userId);

        if ($verifyAction->exec($user, $this->code)) {
            Auth::login($user, session('auth.2fa.remember', false));

            session()->forget(['auth.2fa.id', 'auth.2fa.remember']);
            session()->regenerate();

            $user->update([
                'last_login_at' => Date::now(),
                'ip_address' => request()->ip(),
            ]);

            Toaster::success(__('auth.status.logged_in'));

            $this->redirectRoute('dashboard.index', navigate: true);
        } else {
            $this->addError('code', 'O código informado é inválido.');
        }
    }

    public function toggleRecovery(): void
    {
        $this->recovery = !$this->recovery;
        $this->code = '';
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.auth.two-factor-challenge');
    }
}
