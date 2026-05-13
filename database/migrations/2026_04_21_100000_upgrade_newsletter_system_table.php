<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->timestamp('verified_at')->nullable()->after('email');
            $table->string('verification_token')->nullable()->after('verified_at');
            $table->boolean('receive_platform_updates')->default(true)->after('verification_token');
        });

        Schema::create('category_newsletter_subscriber', function (Blueprint $table) {
            $table->id();
            $table->foreignId('newsletter_subscriber_id')
                ->constrained('newsletter_subscribers')
                ->cascadeOnDelete();
            $table->foreignId('post_category_id')
                ->constrained('post_categories')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['newsletter_subscriber_id', 'post_category_id'], 'subscriber_category_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_newsletter_subscriber');
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->dropColumn(['verified_at', 'verification_token', 'receive_platform_updates']);
        });
    }
};
