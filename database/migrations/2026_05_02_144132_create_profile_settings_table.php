<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();

            // UI Customization
            $table->string('button_style')->default('rounded-md'); // rounded, rounded-full, square
            $table->string('card_style')->default('bordered'); // bordered, shadow, flat
            $table->string('layout_type')->default('default'); // default, centered, grid
            $table->string('font_family')->default('sans');

            // Extended Colors
            $table->string('secondary_color')->nullable();
            $table->string('text_color')->nullable();
            $table->string('background_color')->nullable();

            // Boolean toggles
            $table->boolean('show_badges')->default(true);
            $table->boolean('show_subscriber_count')->default(true);
            $table->boolean('show_view_count')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_settings');
    }
};
