<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Esta migration é apenas para garantir que as colunas existam se a anterior falhou por algum motivo silencioso
        if (!Schema::hasColumn('profiles', 'is_verified')) {
            Schema::table('profiles', function (Blueprint $table) {
                $table->boolean('is_verified')->default(false)->after('username');
            });
        }

        if (!Schema::hasColumn('profiles', 'profile_views')) {
            Schema::table('profiles', function (Blueprint $table) {
                $table->unsignedBigInteger('profile_views')->default(0)->after('is_searchable');
            });
        }
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['is_verified', 'profile_views']);
        });
    }
};
