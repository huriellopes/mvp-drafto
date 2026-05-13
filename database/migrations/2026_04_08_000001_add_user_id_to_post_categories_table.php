<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('post_categories', function (Blueprint $table): void {
            $table->foreignId('user_id')
                ->after('id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('post_categories', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
