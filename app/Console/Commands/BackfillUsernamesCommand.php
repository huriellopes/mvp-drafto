<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Profile;
use App\Traits\GeneratesUsername;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('app:backfill-usernames')]
#[Description('Gera um username (a partir do nome) para perfis sem username, evitando quebra de páginas')]
final class BackfillUsernamesCommand extends Command
{
    use GeneratesUsername;

    public function handle(): int
    {
        $profiles = Profile::query()
            ->with('user:id,name')
            ->where(fn ($query) => $query->whereNull('username')
                ->orWhere('username', ''))
            ->get();

        if ($profiles->isEmpty()) {
            $this->components->info('Nenhum perfil sem username encontrado.');

            return self::SUCCESS;
        }

        $count = 0;

        foreach ($profiles as $profile) {
            $base = $profile->name ?: ($profile->user?->name ?: 'usuario');

            $profile->update([
                'username' => $this->generateUniqueUsername($base),
            ]);

            $count++;
        }

        $this->components->info("{$count} username(s) gerado(s) a partir do nome.");
        Log::info("Backfill de usernames: {$count} perfis atualizados.");

        return self::SUCCESS;
    }
}
