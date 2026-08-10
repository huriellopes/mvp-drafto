<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard\Settings;

use App\Actions\Auth\ConfirmTwoFactorAuthAction;
use App\Actions\Auth\DisableTwoFactorAuthAction;
use App\Actions\Auth\GenerateTwoFactorSecretAction;
use App\Jobs\GenerateRecoveryCodesJob;
use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class TwoFactorManager extends Component
{
    public bool $showingQrCode = false;

    public bool $showingConfirmation = false;

    public bool $showingRecoveryCodes = false;

    public string $code = '';

    public string $currentPassword = '';

    public ?string $generatedPath = null;

    public ?string $generatingFormat = null;

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
        // Segurança: desativar 2FA é uma ação sensível — exige reentrada da
        // senha atual, não só uma confirmação de UI. Sem isto, uma sessão
        // sequestrada (XSS, dispositivo destravado) conseguia desligar o
        // segundo fator com um único clique.
        $this->validate(['currentPassword' => ['required', 'string']]);

        if (!Hash::check($this->currentPassword, Auth::user()->password)) {
            $this->addError('currentPassword', 'Senha incorreta.');

            return;
        }

        $disableAction->exec(Auth::user());

        $this->currentPassword = '';
        $this->showingQrCode = false;
        $this->showingConfirmation = false;
        $this->showingRecoveryCodes = false;

        $this->dispatch('close-modal', name: 'confirm-disable-2fa');

        Toaster::info('Autenticação de dois fatores desativada.');
    }

    public function showRecoveryCodes(): void
    {
        $this->showingRecoveryCodes = !$this->showingRecoveryCodes;
    }

    public function downloadRecoveryCodes(string $format): void
    {
        $user = Auth::user();
        $codes = $user->two_factor_recovery_codes;

        if (empty($codes)) {
            Toaster::error('Nenhum código de recuperação encontrado.');

            return;
        }

        $this->generatingFormat = $format;
        $timestamp = now()->format('d-m-Y');
        $username = Str::slug($user->profile?->username ?? $user->name);
        $filename = "drafto-{$username}-{$timestamp}";
        $this->generatedPath = "temp/{$filename}.{$format}";

        dispatch(new GenerateRecoveryCodesJob($user, $format, $timestamp));

        Toaster::info('Seu arquivo está sendo gerado em segundo plano...');
    }

    #[Computed]
    public function isFileReady(): bool
    {
        if (!$this->generatedPath) {
            return false;
        }

        return Storage::disk('local')->exists($this->generatedPath);
    }

    public function clearGeneratedFile(): void
    {
        $this->generatedPath = null;
        $this->generatingFormat = null;
    }

    #[Computed]
    public function twoFactorQrCodeSvg(): string
    {
        $user = Auth::user();

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd,
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
