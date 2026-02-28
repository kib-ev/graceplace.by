<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $roleExists = DB::table('roles')->where('name', 'manager')->where('guard_name', 'web')->exists();
        if (!$roleExists) {
            DB::table('roles')->insert([
                'name' => 'manager',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $roleId = DB::table('roles')->where('name', 'manager')->where('guard_name', 'web')->value('id');
        if ($roleId) {
            DB::table('model_has_roles')->where('role_id', $roleId)->delete();
            DB::table('roles')->where('id', $roleId)->delete();
        }
    }
};
