<?php

declare(strict_types=1);

namespace App\Exports;

use App\DTOs\ShortLinkFilterData;
use App\Models\Post;
use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ShortLinksExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    public function __construct(
        private readonly ShortLinkFilterData $filters,
    ) {}

    public function query()
    {
        return ShortLink::query()
            ->with(['user', 'shortable'])
            ->when($this->filters->search, function (Builder $query, string $search) {
                $query->where('code', 'like', "%{$search}%")
                    ->orWhereHas('user', function (Builder $q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->orderBy($this->filters->sort, $this->filters->direction);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Código',
            'URL Encurtada',
            'Usuário',
            'E-mail Usuário',
            'Tipo',
            'Destino Original',
            'Cliques',
            'Criado em',
        ];
    }

    /**
     * @param  ShortLink  $shortLink
     */
    public function map($shortLink): array
    {
        $destination = match (true) {
            $shortLink->shortable instanceof Post => $shortLink->shortable->title,
            $shortLink->shortable instanceof User => $shortLink->shortable->profile?->username ?? $shortLink->shortable->name,
            default => 'Desconhecido',
        };

        return [
            $shortLink->id,
            $shortLink->code,
            route('shortlink.redirect', $shortLink->code),
            $shortLink->user->name,
            $shortLink->user->email,
            $shortLink->shortable_type === Post::class ? 'Post' : 'Perfil',
            $destination,
            $shortLink->clicks,
            $shortLink->created_at->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
