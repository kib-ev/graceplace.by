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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_id')->nullable();
            $table->foreignId('place_id')->nullable();
            $table->foreignId('client_id')->nullable();
            $table->dateTime('date')->nullable();
            $table->integer('duration')->nullable(); // minutes
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
