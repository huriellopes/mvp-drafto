<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Tag;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        if (app()->isProduction()) {
            $tags = [
                ['name' => 'Laravel', 'slug' => 'laravel'],
                ['name' => 'PHP', 'slug' => 'php'],
                ['name' => 'Tailwind', 'slug' => 'tailwind'],
                ['name' => 'Livewire', 'slug' => 'livewire'],
                ['name' => 'Alpine.js', 'slug' => 'alpine-js'],
                ['name' => 'SEO', 'slug' => 'seo'],
                ['name' => 'Writing', 'slug' => 'writing'],
            ];

            foreach ($tags as $tag) {
                Tag::query()->create($tag) ;
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
