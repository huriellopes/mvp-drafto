<?php

declare(strict_types=1);

namespace App\Actions\Support;

use App\DTOs\SupportContactData;
use App\Jobs\ProcessSupportMessageJob;

class SendSupportMessageAction
{
    /**
     * Executa o processamento do envio da mensagem de suporte.
     * Sênior: Despacha para fila para não travar a UI do usuário.
     */
    public function exec(SupportContactData $data): void
    {
        ProcessSupportMessageJob::dispatch($data);
    }
}
