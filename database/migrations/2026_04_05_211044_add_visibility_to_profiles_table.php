<?php

declare(strict_types=1);

use App\Enums\ProfileVisibilityEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('visibility')
                ->default(ProfileVisibilityEnum::PUBLIC->value)
                ->after('theme_mode');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('visibility');
        });
    }
};
