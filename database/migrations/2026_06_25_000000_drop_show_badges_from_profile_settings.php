<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('profile_settings', function (Blueprint $table) {
            $table->dropColumn('show_badges');
        });
    }

    public function down(): void
    {
        Schema::table('profile_settings', function (Blueprint $table) {
            $table->boolean('show_badges')->default(true);
        });
    }
};
