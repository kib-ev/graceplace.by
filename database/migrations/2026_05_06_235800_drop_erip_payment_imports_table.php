<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('erip_payments', function (Blueprint $table) {
            if (Schema::hasColumn('erip_payments', 'erip_payment_import_id')) {
                $table->dropForeign(['erip_payment_import_id']);
                $table->dropColumn('erip_payment_import_id');
            }
        });

        Schema::dropIfExists('erip_payment_imports');
    }

    public function down(): void
    {
        Schema::create('erip_payment_imports', function (Blueprint $table) {
            $table->id();
            $table->string('original_filename');
            $table->date('report_month')->nullable();
            $table->unsignedBigInteger('imported_by_user_id')->nullable();
            $table->unsignedInteger('rows_total')->default(0);
            $table->unsignedInteger('rows_inserted')->default(0);
            $table->unsignedInteger('rows_skipped')->default(0);
            $table->timestamps();

            $table->foreign('imported_by_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::table('erip_payments', function (Blueprint $table) {
            $table->foreignId('erip_payment_import_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();
        });
    }
};
