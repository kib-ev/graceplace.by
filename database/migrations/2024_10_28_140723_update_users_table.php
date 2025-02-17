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
        Schema::table('users', function (Blueprint $table) {
            $table->dateTime('offer_accept_date')->nullable()->after('remember_token');
            $table->decimal('real_balance', 10, 2)->default(0)->after('remember_token');
            $table->decimal('bonus_balance', 10, 2)->default(0)->after('remember_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('real_balance');
            $table->dropColumn('bonus_balance');
            $table->dropColumn('offer_accept_date');
        });
    }
};
