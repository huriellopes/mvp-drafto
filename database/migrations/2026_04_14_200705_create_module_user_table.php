<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('module_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->index()
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('module_id')
                ->index()
                ->constrained()
                ->cascadeOnDelete();

            // Controle específico por usuário
            $table->boolean('is_enabled')->default(true);

            // Permite que o usuário tenha limites ou chaves de API próprias
            // sobrepondo as globais do módulo
            $table->json('settings')->nullable();

            $table->timestamps();

            // Sênior: Índice único para evitar duplicidade de vínculo
            $table->unique(['user_id', 'module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_user');
    }
};
