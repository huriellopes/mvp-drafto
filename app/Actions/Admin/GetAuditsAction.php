<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\DTOs\AuditFilterData;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Models\Audit;

final class GetAuditsAction
{
    /**
     * Execute the action to get filtered and paginated audits.
     */
    public function exec(AuditFilterData $dto): LengthAwarePaginator
    {
        return $this->query($dto)
            ->paginate($dto->perPage);
    }

    /**
     * Get the base query for audits with filters applied.
     */
    public function query(AuditFilterData $dto): Builder
    {
        return Audit::query()
            ->with('user')
            ->when($dto->userId, fn (Builder $q) => $q->where('user_id', $dto->userId))
            ->when($dto->event, fn (Builder $q) => $q->where('event', $dto->event))
            ->when($dto->auditableType, fn (Builder $q) => $q->where('auditable_type', $dto->auditableType))
            ->when($dto->startDate, fn (Builder $q) => $q->where('created_at', '>=', $dto->startDate))
            ->when($dto->endDate, fn (Builder $q) => $q->where('created_at', '<=', $dto->endDate))
            ->latest();
    }

    /**
     * Get unique events from audits for filtering.
     * Cached for 1 hour to improve performance.
     */
    public function getUniqueEvents(): Collection
    {
        return collect(Cache::remember('audit_unique_events_v3', now()->addHour(), function () {
            return Audit::query()->distinct()->pluck('event')->toArray();
        }));
    }

    /**
     * Get unique auditable types from audits for filtering.
     * Cached for 1 hour to improve performance.
     */
    public function getUniqueTypes(): Collection
    {
        return collect(Cache::remember('audit_unique_types_v3', now()->addHour(), function () {
            return Audit::query()
                ->distinct()
                ->pluck('auditable_type')
                ->filter()
                ->map(fn ($type) => [
                    'value' => $type,
                    'label' => str_replace('App\\Models\\', '', (string) $type),
                ])
                ->values()
                ->toArray();
        }));
    }

    /**
     * Get users who have performed actions (audits).
     */
    public function getAvailableUsers(): Collection
    {
        return collect(Cache::remember('audit_available_users_v3', now()->addHour(), function () {
            return User::query()
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('audits')
                        ->whereColumn('audits.user_id', 'users.id');
                })
                ->orderBy('name')
                ->get(['id', 'name'])
                ->toArray();
        }));
    }
}
