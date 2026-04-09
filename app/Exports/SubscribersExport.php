<?php

declare(strict_types=1);

namespace App\Exports;

use App\DTOs\NewsletterFilterData;
use App\Models\NewsletterSubscriber;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SubscribersExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(
        private readonly NewsletterFilterData $filters,
    ) {}

    public function query()
    {
        return NewsletterSubscriber::query()
            ->with('category')
            ->when($this->filters->search, function (Builder $query, string $search) {
                $query->where('email', 'like', "%{$search}%");
            })
            ->when($this->filters->category_id, function (Builder $query, int $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->orderBy($this->filters->sort, $this->filters->direction);
    }

    public function headings(): array
    {
        return ['ID', 'E-mail', 'Categoria de Interesse', 'Data de Inscrição'];
    }

    public function map($subscriber): array
    {
        return [
            $subscriber->id,
            $subscriber->email,
            $subscriber->category?->name ?? 'Geral',
            $subscriber->created_at->translatedFormat('d/m/Y H:i'),
        ];
    }
}
