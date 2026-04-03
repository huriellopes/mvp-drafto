<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReportReasonEnum;
use App\Enums\ReportStatusEnum;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        return [
            'reporter_id' => User::factory()->active(),
            'reportable_type' => Post::class,
            'reportable_id' => null,
            'reason' => fake()->randomElement(ReportReasonEnum::cases()),
            'description' => fake()->optional()->sentence(),
            'status' => ReportStatusEnum::PENDING,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Report $report): void {
            if ($report->reportable_id !== null) {
                return;
            }

            $post = Post::factory()->published()->create();

            $report->reportable_type = Post::class;
            $report->reportable_id = $post->id;
        });
    }

    public function pending(): static
    {
        return $this->state(fn (): array => [
            'status' => ReportStatusEnum::PENDING,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);
    }

    public function reviewed(?User $reviewer = null): static
    {
        return $this->state(fn (): array => [
            'status' => ReportStatusEnum::REVIEWED,
            'reviewed_by' => $reviewer?->id ?? User::factory()->superAdmin(),
            'reviewed_at' => now(),
        ]);
    }

    public function dismissed(?User $reviewer = null): static
    {
        return $this->state(fn (): array => [
            'status' => ReportStatusEnum::DISMISSED,
            'reviewed_by' => $reviewer?->id ?? User::factory()->superAdmin(),
            'reviewed_at' => now(),
        ]);
    }

    public function actionTaken(?User $reviewer = null): static
    {
        return $this->state(fn (): array => [
            'status' => ReportStatusEnum::ACTION_TAKEN,
            'reviewed_by' => $reviewer?->id ?? User::factory()->superAdmin(),
            'reviewed_at' => now(),
        ]);
    }

    public function forPost(?Post $post = null): static
    {
        return $this->state(function () use ($post): array {
            $post ??= Post::factory()->published()->create();

            return [
                'reportable_type' => Post::class,
                'reportable_id' => $post->id,
            ];
        });
    }

    public function forComment(?Comment $comment = null): static
    {
        return $this->state(function () use ($comment): array {
            $comment ??= Comment::factory()->create();

            return [
                'reportable_type' => Comment::class,
                'reportable_id' => $comment->id,
            ];
        });
    }

    public function byReporter(User $user): static
    {
        return $this->state(fn (): array => [
            'reporter_id' => $user->id,
        ]);
    }

    public function reason(ReportReasonEnum $reason): static
    {
        return $this->state(fn (): array => [
            'reason' => $reason,
        ]);
    }
}
