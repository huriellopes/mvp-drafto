<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'slug']);
        });

        Schema::table('saved_posts', function (Blueprint $table): void {
            $table->foreignId('collection_id')
                ->nullable()
                ->after('post_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('saved_posts', fn (Blueprint $table) => $table->dropColumn('collection_id'));
        Schema::dropIfExists('collections');
    }
};
