<?php

declare(strict_types=1);

use App\Enums\PostStatusEnum;
use App\Enums\PostTypeEnum;
use App\Enums\PostVisibilityEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('category_id')
                ->nullable()
                ->constrained('post_categories')
                ->nullOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');

            $table->string('type')
                ->default(PostTypeEnum::POST->value);

            $table->string('cover_image_path')->nullable();

            $table->string('status')
                ->default(PostStatusEnum::DRAFT->value);

            $table->string('visibility')
                ->default(PostVisibilityEnum::PUBLIC->value);

            $table->timestamp('published_at')->nullable();
            $table->timestamp('featured_at')->nullable();

            $table->boolean('comments_enabled')->default(true);
            $table->unsignedInteger('reading_time')->default(1);

            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('likes_count')->default(0);
            $table->unsignedBigInteger('comments_count')->default(0);

            $table->timestamps();

            $table->index(['status', 'published_at']);
            $table->index(['user_id', 'status']);
            $table->index(['visibility', 'status']);
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
