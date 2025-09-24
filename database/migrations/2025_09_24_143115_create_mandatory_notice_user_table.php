<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mandatory_notice_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mandatory_notice_id')->constrained('mandatory_notices')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->unique(['mandatory_notice_id', 'user_id']);
            $table->index(['user_id', 'confirmed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mandatory_notice_user');
    }
};
