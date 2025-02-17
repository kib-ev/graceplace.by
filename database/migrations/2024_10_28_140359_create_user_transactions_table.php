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
        Schema::create('user_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2); // Сумма изменения
            $table->string('type'); // Тип транзакции: 'deposit', 'withdrawal', 'refund'
            $table->string('description')->nullable(); // Описание транзакции
            $table->decimal('balance_after', 10, 2); // Баланс после транзакции
//            $table->string('payment_method')->nullable(); // Поле для хранения типа оплаты
//            $table->string('payment_category')->nullable(); // Поле для хранения категории оплаты
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_transactions');
    }
};
