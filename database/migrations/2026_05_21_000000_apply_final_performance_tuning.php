<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('site_views', function (Blueprint $table) {
            $table->index(['viewed_at', 'url'], 'site_views_analytics_url_index');
            $table->index(['viewed_at', 'session_id'], 'site_views_analytics_session_index');
            $table->index(['viewed_at', 'search_query'], 'site_views_analytics_search_index');
        });

        Schema::table('audits', function (Blueprint $table) {
            $table->index(['created_at', 'event'], 'audits_created_at_event_index');
            $table->index(['created_at', 'user_id'], 'audits_created_at_user_index');
            $table->index(['auditable_type', 'auditable_id'], 'audits_morph_index');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->index(['user_id', 'status', 'created_at'], 'posts_user_status_listing_index');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['notifiable_id', 'notifiable_type', 'read_at'], 'notifications_unread_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_unread_index');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_user_status_listing_index');
        });

        Schema::table('audits', function (Blueprint $table) {
            $table->dropIndex('audits_created_at_event_index');
            $table->dropIndex('audits_created_at_user_index');
            $table->dropIndex('audits_morph_index');
        });

        Schema::table('site_views', function (Blueprint $table) {
            $table->dropIndex('site_views_analytics_url_index');
            $table->dropIndex('site_views_analytics_session_index');
            $table->dropIndex('site_views_analytics_search_index');
        });
    }
};
