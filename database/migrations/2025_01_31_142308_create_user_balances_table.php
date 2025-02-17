<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('user_balances', function (Blueprint $table) {
            $table->id(); // Уникальный идентификатор записи
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Связь с users
            $table->enum('balance_type', ['real', 'bonus', 'cashback']); // Тип баланса
            $table->decimal('amount', 18, 2)->default(0.00); // Значение баланса
            $table->string('currency', 3)->default('BYN'); // Валюта баланса
            $table->enum('status', ['active', 'blocked', 'expired'])->default('active'); // Статус баланса
            $table->timestamp('expiration_date')->nullable(); // Срок действия для бонусов/кешбэка
            $table->json('metadata')->nullable(); // Дополнительные данные в JSON
            $table->timestamps(); // created_at и updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_balances');
    }
};
