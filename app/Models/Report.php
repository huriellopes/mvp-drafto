<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReportReasonEnum;
use App\Enums\ReportStatusEnum;
use Database\Factories\ReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Override;
use OwenIt\Auditing\Contracts\Auditable;

#[Fillable([
    'reporter_id',
    'reportable_type',
    'reportable_id',
    'reason',
    'description',
    'admin_feedback',
    'status',
    'reviewed_by',
    'reviewed_at',
])]
class Report extends Model implements Auditable
{
    /** @use HasFactory<ReportFactory> */
    use HasFactory, \OwenIt\Auditing\Auditable;

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'reason' => ReportReasonEnum::class,
            'status' => ReportStatusEnum::class,
            'reviewed_at' => 'datetime',
        ];
    }
}
