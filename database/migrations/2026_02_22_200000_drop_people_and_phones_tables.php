<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Снимаем FK только если он реально существует
        $fkExists = DB::select("
            SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'masters'
              AND COLUMN_NAME = 'person_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ");

        if (!empty($fkExists)) {
            $fkName = $fkExists[0]->CONSTRAINT_NAME;
            DB::statement("ALTER TABLE `masters` DROP FOREIGN KEY `{$fkName}`");
        }

        if (Schema::hasColumn('masters', 'person_id')) {
            Schema::table('masters', function (Blueprint $table) {
                $table->dropColumn('person_id');
            });
        }

        Schema::dropIfExists('phones');
        Schema::dropIfExists('people');
    }

    public function down(): void
    {
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('patronymic')->nullable();
            $table->date('birth_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('phones', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->unsignedBigInteger('person_id')->nullable();
            $table->timestamps();
            $table->foreign('person_id')->references('id')->on('people');
        });

        Schema::table('masters', function (Blueprint $table) {
            $table->unsignedBigInteger('person_id')->nullable()->after('user_id');
            $table->foreign('person_id')->references('id')->on('people');
        });
    }
};
