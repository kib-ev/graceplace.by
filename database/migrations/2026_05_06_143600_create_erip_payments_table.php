<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erip_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('erip_payment_import_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('row_number');

            $table->string('status')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2)->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->string('operation_number')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payer_raw')->nullable();
            $table->string('payer_phone')->nullable();
            $table->string('payer_name')->nullable();
            $table->dateTime('invoice_created_at')->nullable();
            $table->string('account_number')->nullable();
            $table->string('terminal_sn')->nullable();
            $table->string('merchant_code')->nullable();
            $table->json('raw_row')->nullable();
            $table->string('fingerprint')->unique();
            $table->timestamps();

            $table->index('paid_at');
            $table->index('operation_number');
            $table->index('payer_phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erip_payments');
    }
};
