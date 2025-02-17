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
        Schema::table('storage_cells', function (Blueprint $table) {
            $table->renameColumn('name', 'number');
            $table->string('secret')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('storage_cells', function (Blueprint $table) {
            $table->renameColumn('number', 'name');
            $table->dropColumn('secret');
        });
    }
};
