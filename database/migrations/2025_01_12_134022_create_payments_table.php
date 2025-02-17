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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('payable'); // Связь с моделями Appointment и StorageCell
            $table->decimal('amount', 10, 2); // Сумма платежа
//            $table->decimal('refunded_amount', 10, 2)->default(0); // Сумма возврата
            $table->enum('status', ['pending', 'completed', 'refunded', 'cancelled']);
            $table->enum('payment_method', ['cash', 'card', 'service', 'bonus', 'other']); // Массив методов оплаты (наличные, карта и т.д.)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
