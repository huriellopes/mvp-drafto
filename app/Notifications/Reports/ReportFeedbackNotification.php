<?php

declare(strict_types=1);

namespace App\Notifications\Reports;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

final class ReportFeedbackNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Report $report,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Atualização sobre sua denúncia - ' . config('app.name'))
            ->greeting("Olá, {$notifiable->name}!")
            ->line('Sua denúncia sobre um conteúdo de ' . class_basename($this->report->reportable_type) . ' foi revisada por nossa equipe.')
            ->line('Status atual: **' . $this->report->status->label() . '**')
            ->line('Mensagem da moderação: "' . $this->report->admin_feedback . '"')
            ->line('Obrigado por nos ajudar a manter a comunidade segura.')
            ->action('Ver Diretrizes', url('/diretrizes'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'report_id' => $this->report->id,
            'status' => $this->report->status->value,
            'message' => 'Sua denúncia foi revisada.',
        ];
    }
}
