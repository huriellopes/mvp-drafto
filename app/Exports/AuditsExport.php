<?php

declare(strict_types=1);

namespace App\Exports;

use App\Actions\Admin\GetAuditsAction;
use App\DTOs\AuditFilterData;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

final readonly class AuditsExport implements FromQuery, WithChunkReading, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(
        private AuditFilterData $filters,
    ) {}

    public function query(): Builder
    {
        return (new GetAuditsAction())->query($this->filters);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Usuário',
            'Evento',
            'Modelo',
            'ID Modelo',
            'IP',
            'Data',
            'Valores Antigos',
            'Valores Novos',
        ];
    }

    public function map($audit): array
    {
        return [
            $audit->id,
            $audit->user?->name ?? 'Sistema',
            $audit->event,
            str_replace('App\\Models\\', '', (string) $audit->auditable_type),
            $audit->auditable_id,
            $audit->ip_address,
            $audit->created_at->format('d/m/Y H:i:s'),
            json_encode($audit->old_values),
            json_encode($audit->new_values),
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
