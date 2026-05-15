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
        if (config('database.default') === 'sqlite') {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->fullText(['title', 'excerpt', 'content']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->fullText(['name', 'email']);
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->fullText(['username', 'name', 'bio']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') === 'sqlite') {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->dropFullText(['title', 'excerpt', 'content']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropFullText(['name', 'email']);
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->dropFullText(['username', 'name', 'bio']);
        });
    }
};
