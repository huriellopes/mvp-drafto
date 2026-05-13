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
            $table->string('name')
                ->nullable()
                ->index();
            $table->string('username')->unique()->index();
            $table->string('email')
                ->index()
                ->nullable()
                ->unique();
            $table->text('bio', 160)->nullable();
            $table->string('avatar_path')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('website_url')->nullable();
            $table->string('location')->nullable();
            $table->string('theme_mode')->default(ThemePlatformEnum::SYSTEM->value);
            $table->string('primary_color', 7)->default('#18181b');
            $table->string('accent_color', 7)->default('#3f3f46');
            $table->boolean('show_email_publicly')->default(false);
            $table->boolean('is_searchable')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
