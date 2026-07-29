<?php

declare(strict_types=1);

use App\Models\Module;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name')
                ->index();
            $table->string('slug')
                ->index()
                ->unique(); // ex: 'newsletter', 'comments', 'ai_assistant'
            $table->string('description')->nullable();
            $table->string('icon')->default('package');
            $table->boolean('is_enabled')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        if (app()->isProduction()) {
            $modules = config('modules');

            foreach ($modules as $module) {
                Module::query()->create($module);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
