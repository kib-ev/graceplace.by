<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('work_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();

            // Индекс для быстрого поиска по дате и пользователю
            $table->index(['user_id', 'work_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_schedules');
    }
}; 