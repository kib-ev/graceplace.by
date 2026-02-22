<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('masters', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('user_id');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('patronymic')->nullable()->after('last_name');
            $table->date('birth_date')->nullable()->after('patronymic');
        });

        // Копируем данные из people в masters
        DB::statement('
            UPDATE masters
            INNER JOIN people ON people.id = masters.person_id
            SET
                masters.first_name = people.first_name,
                masters.last_name  = people.last_name,
                masters.patronymic = people.patronymic,
                masters.birth_date = people.birth_date
        ');
    }

    public function down(): void
    {
        Schema::table('masters', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'patronymic', 'birth_date']);
        });
    }
};
