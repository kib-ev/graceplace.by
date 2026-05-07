<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erip_payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('erip_payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['erip_payment_id', 'payment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erip_payment_allocations');
    }
};
