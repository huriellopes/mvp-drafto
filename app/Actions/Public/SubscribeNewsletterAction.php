<?php

declare(strict_types=1);

namespace App\Actions\Public;

use App\Contracts\Services\LoggerInterface;
use App\DTOs\Public\NewsletterData;
use App\Enums\LogCategoryEnum;
use App\Mail\NewsletterVerificationMail;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

final class SubscribeNewsletterAction
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function exec(NewsletterData $data): void
    {
        $this->logger->info("Tentativa de inscrição na newsletter: {$data->email}", LogCategoryEnum::AUDIT);

        $subscriber = NewsletterSubscriber::firstOrCreate(
            ['email' => $data->email],
            [
                'verification_token' => Str::random(40),
                'verified_at' => null, // Sênior: Sempre nulo em nova inscrição para forçar double opt-in
            ],
        );

        // Se o inscrito já existia e estava verificado, não alteramos o token nem a data de verificação
        // mas permitimos que ele atualize seus interesses de categoria
        if ($data->categoryId) {
            $subscriber->categories()->syncWithoutDetaching([$data->categoryId]);
        }

        // Se não estiver verificado, dispara o e-mail de confirmação
        if (!$subscriber->isVerified()) {
            Mail::to($subscriber->email)->send(new NewsletterVerificationMail($subscriber));
        }
    }
}
