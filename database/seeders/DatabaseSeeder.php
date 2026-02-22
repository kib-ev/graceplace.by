<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Master;
use App\Models\Place;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
       Master::factory(5)->create();
       Place::factory(6)->create();
       Appointment::factory(10)->create();
    }
}
