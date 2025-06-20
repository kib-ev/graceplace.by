<?php

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
        Schema::table('appointments', function (Blueprint $table) {
            $table->index(['place_id', 'start_at'], 'appointments_performance_index');
        });

        Schema::table('places', function (Blueprint $table) {
            $table->index(['is_hidden', 'sort'], 'places_performance_index');
        });

        Schema::table('user_settings', function (Blueprint $table) {
            $table->index(['user_id', 'key'], 'user_settings_performance_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_performance_index');
        });

        Schema::table('places', function (Blueprint $table) {
            $table->dropIndex('places_performance_index');
        });

        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropIndex('user_settings_performance_index');
        });
    }
}; 