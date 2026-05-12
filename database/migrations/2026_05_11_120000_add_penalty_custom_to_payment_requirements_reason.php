<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE payment_requirements MODIFY COLUMN reason ENUM('default','penalty_50','penalty_100','penalty_custom') NOT NULL DEFAULT 'default'");
    }

    public function down(): void
    {
        DB::statement("UPDATE payment_requirements SET reason = 'penalty_100' WHERE reason = 'penalty_custom'");
        DB::statement("ALTER TABLE payment_requirements MODIFY COLUMN reason ENUM('default','penalty_50','penalty_100') NOT NULL DEFAULT 'default'");
    }
};
