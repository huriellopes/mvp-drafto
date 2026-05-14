<?php

declare(strict_types=1);

namespace App\Exports;

use App\DTOs\UserFilterData;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    public function __construct(
        private readonly UserFilterData $filters,
    ) {}

    public function query()
    {
        return User::query()
            ->with(['profile'])
            ->when($this->filters->search, function (Builder $query, string $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($this->filters->role, fn ($query, $role) => $query->where('role', $role))
            ->when($this->filters->status, fn ($query, $status) => $query->where('status', $status))
            ->orderBy($this->filters->sort, $this->filters->direction);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nome Completo',
            'E-mail',
            'Papel (Role)',
            'Status',
            'Membro Desde',
            'Último Login',
        ];
    }

    /**
     * @param  User  $user
     */
    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->role->label(),
            $user->status->label(),
            $user->created_at->format('d/m/Y'),
            $user->last_login_at?->format('d/m/Y H:i') ?? 'Nunca',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Cabeçalho em negrito
        ];
    }
}
