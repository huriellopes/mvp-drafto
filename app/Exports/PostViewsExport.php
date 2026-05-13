<?php

declare(strict_types=1);

namespace App\Exports;

use App\DTOs\PostViewFilterData;
use App\Models\PostView;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PostViewsExport implements FromQuery, ShouldQueue, WithChunkReading, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(
        private readonly PostViewFilterData $filters,
    ) {}

    public function query()
    {
        return PostView::query()
            ->select('id', 'post_id', 'user_id', 'viewed_at', 'ip_hash', 'user_agent')
            ->with([
                'post:id,title',
                'user:id,name',
            ])
            ->when($this->filters->search, function (Builder $query, string $search) {
                $query->where(function (Builder $q) use ($search) {
                    $q->whereHas('post', fn ($p) => $p->where('title', 'like', "%{$search}%"))
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                        ->orWhere('ip_hash', $search);
                });
            })
            ->orderBy($this->filters->sort, $this->filters->direction);
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function headings(): array
    {
        return [
            'ID Registro',
            'Título do Post',
            'Nome do Leitor',
            'Data e Hora da Visualização',
            'Endereço IP (Descriptografado)',
            'Navegador / Dispositivo',
        ];
    }

    /**
     * Mapeamento linha a linha do Excel.
     *
     * @param  PostView  $view
     */
    public function map($view): array
    {
        return [
            $view->id,
            $view->post?->title ?? 'N/A',
            $view->user?->name ?? 'Visitante Anônimo',
            $view->viewed_at->format('d/m/Y H:i:s'),
            $view->ip_hash,
            $view->user_agent,
        ];
    }
}
