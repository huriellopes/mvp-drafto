<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Post Likes
        Schema::table('post_likes', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();

            if (!Schema::hasColumn('post_likes', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('user_id');
            }
        });

        // Comment Likes
        Schema::table('comment_likes', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();

            if (!Schema::hasColumn('comment_likes', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('user_id');
            }
        });

        // Unique constraints are tricky to do conditionally with indexes that might already exist or fail to drop
        // We will do them in a fresh try-catch or simply ignore errors if we know we are just fixing a half-baked state
        try {
            Schema::table('post_likes', function (Blueprint $table) {
                $table->dropUnique(['post_id', 'user_id']);
                $table->unique(['post_id', 'user_id', 'ip_address']);
            });
        } catch (Exception $e) {
            // Already dropped or already exists
        }

        try {
            Schema::table('comment_likes', function (Blueprint $table) {
                $table->dropUnique(['comment_id', 'user_id']);
                $table->unique(['comment_id', 'user_id', 'ip_address']);
            });
        } catch (Exception $e) {
            // Already dropped or already exists
        }
    }

    public function down(): void
    {
        Schema::table('post_likes', function (Blueprint $table) {
            try {
                $table->dropUnique(['post_id', 'user_id', 'ip_address']);
                $table->unique(['post_id', 'user_id']);
            } catch (Exception $e) {
            }

            $table->dropColumn('ip_address');
            $table->foreignId('user_id')->nullable(false)->change();
        });

        Schema::table('comment_likes', function (Blueprint $table) {
            try {
                $table->dropUnique(['comment_id', 'user_id', 'ip_address']);
                $table->unique(['comment_id', 'user_id']);
            } catch (Exception $e) {
            }

            $table->dropColumn('ip_address');
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
