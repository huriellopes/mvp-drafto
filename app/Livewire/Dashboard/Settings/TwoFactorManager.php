<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Settings;

use App\Actions\Auth\ConfirmTwoFactorAuthAction;
use App\Actions\Auth\DisableTwoFactorAuthAction;
use App\Actions\Auth\GenerateTwoFactorSecretAction;
use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class TwoFactorManager extends Component
{
    public bool $showingQrCode = false;

    public bool $showingConfirmation = false;

    public bool $showingRecoveryCodes = false;

    public string $code = '';

    public function enable(GenerateTwoFactorSecretAction $generateAction): void
    {
        $generateAction->exec(Auth::user());

        $this->showingQrCode = true;
        $this->showingConfirmation = true;
    }

    public function confirm(ConfirmTwoFactorAuthAction $confirmAction): void
    {
        $user = Auth::user();

        if ($confirmAction->exec($user, $this->code)) {
            $this->showingQrCode = false;
            $this->showingConfirmation = false;
            $this->showingRecoveryCodes = true;
            $this->code = '';

            Toaster::success('Autenticação de dois fatores confirmada com sucesso.');
        } else {
            Toaster::error('O código informado é inválido.');
        }
    }

    public function disable(DisableTwoFactorAuthAction $disableAction): void
    {
        $disableAction->exec(Auth::user());

        $this->showingQrCode = false;
        $this->showingConfirmation = false;
        $this->showingRecoveryCodes = false;

        Toaster::info('Autenticação de dois fatores desativada.');
    }

    public function showRecoveryCodes(): void
    {
        $this->showingRecoveryCodes = !$this->showingRecoveryCodes;
    }

    public function getTwoFactorQrCodeSvgProperty(): string
    {
        $user = Auth::user();

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd(),
        );

        $writer = new Writer($renderer);

        return $writer->writeString(
            $this->getTwoFactorQrCodeUrl($user),
        );
    }

    public function render()
    {
        return view('livewire.dashboard.settings.two-factor-manager', [
            'user' => Auth::user(),
        ]);
    }

    private function getTwoFactorQrCodeUrl(User $user): string
    {
        return sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s',
            config('app.name'),
            $user->email,
            $user->two_factor_secret,
            config('app.name'),
        );
    }
}
