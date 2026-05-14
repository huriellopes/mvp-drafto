<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'plan_id')) {
                try {
                    $table->dropForeign(['plan_id']);
                } catch (\Exception $e) {
                    // FK pode não existir ou já ter sido removida em tentativas falhas anteriores
                }
            }
        });

        Schema::dropIfExists('subscription_items');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'stripe_id')) {
                try {
                    $table->dropIndex(['stripe_id']);
                } catch (\Exception $e) {}
            }

            $table->dropColumn([
                'stripe_id',
                'pm_type',
                'pm_last_four',
                'trial_ends_at',
                'trial_notification_sent_at',
                'is_lifetime',
                'plan_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Operação destrutiva para limpeza de legado
    }
};
