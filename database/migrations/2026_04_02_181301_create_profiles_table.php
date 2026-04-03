<?php

declare(strict_types=1);

use App\Enums\ThemePlatformEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();
            $table->string('username')->unique();
            $table->text('bio')->nullable();
            $table->string('avatar_path')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('website_url')->nullable();
            $table->string('location')->nullable();
            $table->string('theme_mode')->default(ThemePlatformEnum::SYSTEM->value);
            $table->string('primary_color', 20)->nullable();
            $table->string('accent_color', 20)->nullable();
            $table->boolean('show_email_publicly')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
