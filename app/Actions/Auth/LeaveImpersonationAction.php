<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\ImpersonationLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

final class LeaveImpersonationAction
{
    /**
     * Sênior: Encerra a impersonação e retorna ao usuário original.
     */
    public function exec(): bool
    {
        $impersonatorId = Session::get('impersonator_id');

        if (!$impersonatorId) {
            return false;
        }

        $admin = User::find($impersonatorId);
        $impersonatedId = auth()->id();

        if (!$admin) {
            return false;
        }

        // Auditoria: Finaliza o log
        ImpersonationLog::query()
            ->where('impersonator_id', $admin->id)
            ->where('impersonated_id', $impersonatedId)
            ->whereNull('ended_at')
            ->latest()
            ->update(['ended_at' => now()]);

        // Realiza o login de volta como o administrador
        Auth::login($admin);

        // Remove a flag de impersonação
        Session::forget('impersonator_id');

        return true;
    }
}
