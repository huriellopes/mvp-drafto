<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Coleções de obras do escritor (séries, ensinamentos, contos...).
        // Distinto de "collections" (Salvos), que agrupa bookmarks de terceiros.
        Schema::create('post_collections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('visibility')->default('private');
            $table->timestamps();

            $table->unique(['user_id', 'slug']);
        });

        // Pivot N:N: um post pode estar em várias coleções e vice-versa.
        Schema::create('post_collection_post', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_collection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['post_collection_id', 'post_id']);
            $table->index('post_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_collection_post');
        Schema::dropIfExists('post_collections');
    }
};
