<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Preferências de comunicação (opt-out por tipo).
            $table->boolean('wants_reengagement_emails')->default(true)->after('last_login_at');
            $table->boolean('wants_product_updates')->default(true)->after('wants_reengagement_emails');

            // Controle do e-mail de retorno (win-back) escalonado.
            $table->timestamp('reengagement_sent_at')->nullable()->after('wants_product_updates');
            // Maior faixa (em dias) já enviada: 15, 30 ou 60. NULL = nenhuma.
            $table->unsignedSmallInteger('reengagement_stage')->nullable()->after('reengagement_sent_at');

            $table->index('reengagement_stage');
        });

        Schema::create('platform_updates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('content');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedInteger('recipients_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_updates');

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['reengagement_stage']);
            $table->dropColumn([
                'wants_reengagement_emails',
                'wants_product_updates',
                'reengagement_sent_at',
                'reengagement_stage',
            ]);
        });
    }
};
