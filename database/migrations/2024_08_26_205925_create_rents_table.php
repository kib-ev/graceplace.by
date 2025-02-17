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
        Schema::create('rents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unsigned()->index();
            $table->string('model_class')->index()->index();
            $table->integer('model_id')->unsigned()->index();
            $table->text('description')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->integer('duration')->comment('days');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rents');
    }
};
