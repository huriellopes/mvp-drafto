<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->index('published_at');
            $table->index('status');
        });

        Schema::table('post_views', function (Blueprint $table) {
            $table->index('viewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('post_views', function (Blueprint $table) {
            $table->dropIndex(['viewed_at']);
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['published_at']);
            $table->dropIndex(['status']);
        });
    }
};
