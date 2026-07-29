<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_views', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('url', 512);
            $table->string('ip_address', 45)->nullable(); // Supports IPv6
            $table->text('user_agent')->nullable();
            $table->string('session_id')->nullable()->index();
            $table->string('search_query')->nullable();
            $table->unsignedInteger('duration')->default(0); // in seconds
            $table->timestamp('viewed_at');
            $table->timestamps();

            // Index with prefix length for MySQL
            $table->index('viewed_at');
            $table->index('url');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_views');
    }
};
