<?php

declare(strict_types=1);

namespace App\Traits\Livewire;

use Masmerise\Toaster\Toaster;

trait HasStandardResponses
{
    /**
     * Sênior: Padroniza o feedback de sucesso no Livewire.
     */
    protected function notifySuccess(string $message): void
    {
        Toaster::success($message);
    }

    /**
     * Sênior: Padroniza o feedback de erro no Livewire.
     */
    protected function notifyError(string $message): void
    {
        Toaster::error($message);
    }

    /**
     * Sênior: Padroniza o feedback de aviso no Livewire.
     */
    protected function notifyWarning(string $message): void
    {
        Toaster::warning($message);
    }

    /**
     * Sênior: Padroniza o feedback de informação no Livewire.
     */
    protected function notifyInfo(string $message): void
    {
        Toaster::info($message);
    }
}
