<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
            $table->index('status');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->index('category_id');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->index('parent_id');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->index('reason');
        });

        Schema::table('saved_posts', function (Blueprint $table) {
            $table->index('collection_id');
        });

        Schema::table('post_tag', function (Blueprint $table) {
            $table->index('tag_id');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->json('data')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->text('data')->change();
        });

        Schema::table('post_tag', function (Blueprint $table) {
            $table->dropIndex(['tag_id']);
        });

        Schema::table('saved_posts', function (Blueprint $table) {
            $table->dropIndex(['collection_id']);
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex(['reason']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['parent_id']);
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['status']);
        });
    }
};
