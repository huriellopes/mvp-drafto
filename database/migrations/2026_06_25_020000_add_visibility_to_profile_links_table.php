<?php

declare(strict_types=1);

use App\Enums\LinkVisibilityEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profile_links', function (Blueprint $table) {
            $table->string('visibility')
                ->default(LinkVisibilityEnum::PUBLIC->value)
                ->after('url');
        });
    }

    public function down(): void
    {
        Schema::table('profile_links', function (Blueprint $table) {
            $table->dropColumn('visibility');
        });
    }
};
