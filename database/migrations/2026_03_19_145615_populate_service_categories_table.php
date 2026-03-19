<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $categories = [
            ['name' => 'Маникюр и педикюр', 'sort' => 1],
            ['name' => 'Парикмахерские услуги', 'sort' => 2],
            ['name' => 'Окрашивание и колористика', 'sort' => 3],
            ['name' => 'Восстановление волос', 'sort' => 4],
            ['name' => 'Косметология', 'sort' => 5],
            ['name' => 'Массаж', 'sort' => 6],
            ['name' => 'Перманентный макияж и тату', 'sort' => 7],
            ['name' => 'Ресницы и брови', 'sort' => 8],
            ['name' => 'Визаж и макияж', 'sort' => 9],
            ['name' => 'Депиляция', 'sort' => 10],
            ['name' => 'Наращивание волос', 'sort' => 11],
            ['name' => 'Плетение и стилистика', 'sort' => 12],
            ['name' => 'Остеопатия и кинезиология', 'sort' => 13],
            ['name' => 'Барбер', 'sort' => 14],
            ['name' => 'Другое / смешанные услуги', 'sort' => 15],
        ];

        $now = now();
        foreach ($categories as $category) {
            $category['created_at'] = $now;
            $category['updated_at'] = $now;
        }

        DB::table('service_categories')->insert($categories);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('master_service_category')->delete();
        DB::table('service_categories')->truncate();
    }
};
