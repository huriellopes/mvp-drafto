<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('profile_settings', function (Blueprint $table) {
            $table->string('avatar_shape')->default('rounded')->after('layout_type');
            $table->string('cover_position')->default('center')->after('avatar_shape');
            $table->string('density')->default('comfortable')->after('cover_position');
            $table->boolean('translucent_background')->default(true)->after('density');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profile_settings', function (Blueprint $table) {
            $table->dropColumn(['avatar_shape', 'cover_position', 'density', 'translucent_background']);
        });
    }
};
