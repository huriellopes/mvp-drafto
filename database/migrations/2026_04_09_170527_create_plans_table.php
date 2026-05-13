<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Free, Plus, Pro
            $table->string('slug')->unique(); // free, plus, pro
            $table->string('stripe_id')->nullable(); // ID do Preço no Stripe
            $table->decimal('price', 8, 2)->default(0);
            $table->json('features')->nullable(); // Lista de benefícios para a UI
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        if (app()->isProduction()) {
            $plans = config('plans');

            foreach ($plans as $plan) {
                \App\Models\Plan::query()->create($plan);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
