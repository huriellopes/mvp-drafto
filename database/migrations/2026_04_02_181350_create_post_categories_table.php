<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PostCategory;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('post_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        if (app()->isProduction()) {
            $categories = [
                ['name' => 'Tecnologia', 'slug' => 'tecnologia'],
                ['name' => 'Negócios', 'slug' => 'negocios'],
                ['name' => 'Produtividade', 'slug' => 'produtividade'],
                ['name' => 'Design', 'slug' => 'design'],
                ['name' => 'Programação', 'slug' => 'programacao'],
                ['name' => 'Carreira', 'slug' => 'carreira'],
                ['name' => 'Marketing', 'slug' => 'marketing'],
                ['name' => 'Opinião', 'slug' => 'opiniao'],
            ];

            foreach ($categories as $category) {
                PostCategory::query()->create($category);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('post_categories');
    }
};
