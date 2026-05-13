<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        // Posts
        $this->addIndexSilently('posts', function (Blueprint $table) {
            $table->index('views_count');
        });

        $this->addIndexSilently('posts', function (Blueprint $table) {
            $table->index('comments_count');
        });

        $this->addIndexSilently('posts', function (Blueprint $table) {
            $table->index('likes_count');
        });

        $this->addIndexSilently('posts', function (Blueprint $table) {
            $table->index(['status', 'visibility', 'published_at'], 'posts_public_listing_index');
        });

        // Post Views
        $this->addIndexSilently('post_views', function (Blueprint $table) {
            $table->index(['post_id', 'session_id', 'viewed_at']);
        });

        $this->addIndexSilently('post_views', function (Blueprint $table) {
            $table->index(['post_id', 'ip_hash', 'viewed_at']);
        });
    }

    public function down(): void
    {
        // Operação de otimização, down não é estritamente necessário para este hotfix
    }

    private function addIndexSilently(string $table, Closure $callback): void
    {
        try {
            Schema::table($table, $callback);
        } catch (Exception $e) {
            // Silencia erro se o índice já existir ou se a tabela não suportar a operação
        }
    }
};
