<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['real_balance', 'bonus_balance']);
        });

        Schema::dropIfExists('user_transactions');
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('real_balance', 10, 2)->default(0)->after('phone');
            $table->decimal('bonus_balance', 10, 2)->default(0)->after('real_balance');
        });

        Schema::create('user_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('amount', 10, 2);
            $table->string('type')->nullable();
            $table->string('transaction_type')->nullable();
            $table->string('description')->nullable();
            $table->decimal('balance_after', 10, 2)->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
};
