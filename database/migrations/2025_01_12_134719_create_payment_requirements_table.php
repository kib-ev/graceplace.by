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
        Schema::create('payment_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Связь с пользователем
            $table->morphs('payable'); // Полиморфная связь с моделями Appointment и StorageCell
            $table->decimal('amount_due', 10, 2); // Сумма к оплате
            $table->date('due_date')->nullable(); // Срок оплаты
            $table->enum('status', ['pending', 'paid', 'overdue']); // Статус требования
            $table->timestamps(); // Временные метки
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_requirements');
    }
};
