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
        Schema::table('payment_requirements', function (Blueprint $table) {
            $table->decimal('expected_amount', 10, 2)->after('amount_due');
            $table->decimal('remaining_amount', 10, 2)->after('expected_amount');
            $table->decimal('price_per_hour_snapshot', 10, 2)->nullable()->after('remaining_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_requirements', function (Blueprint $table) {
            $table->dropColumn(['expected_amount', 'remaining_amount', 'price_per_hour_snapshot']);
        });
    }
};
