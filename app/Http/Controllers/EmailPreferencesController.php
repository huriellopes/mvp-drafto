<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;

class EmailPreferencesController extends Controller
{
    /**
     * Descadastra o usuário de um tipo de e-mail a partir do link assinado
     * presente nos próprios e-mails (não exige login). A rota é protegida pelo
     * middleware 'signed'.
     */
    public function unsubscribe(User $user, string $type): View
    {
        $column = match ($type) {
            'reengagement' => 'wants_reengagement_emails',
            'product_updates' => 'wants_product_updates',
            default => null,
        };

        $label = match ($type) {
            'reengagement' => 'lembretes de retorno',
            'product_updates' => 'avisos de novidades',
            default => 'comunicações',
        };

        if ($column !== null) {
            $user->forceFill([$column => false])->save();
        }

        return view('email-preferences.unsubscribed', [
            'label' => $label,
            'valid' => $column !== null,
        ]);
    }
}
