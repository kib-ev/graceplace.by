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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            // Пользователь, создавший заявку (можно null, если неавторизованный)
            $table->unsignedBigInteger('user_id')->nullable();

            // Заголовок заявки
            $table->string('title');

            // Подробное описание
            $table->text('description')->nullable();

            // Статус заявки
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');

            // Приоритет (опционально)
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');

            // Дополнительное поле: категория проблемы (например, "оборудование", "программное обеспечение", "уборка")
//            $table->string('category')->nullable();

            $table->timestamps();

            // Внешний ключ
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
