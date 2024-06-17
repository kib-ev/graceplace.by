<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Appointment;
use App\Models\Master;
use App\Models\Person;
use App\Models\Place;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
       Person::factory(10)->create();
       Master::factory(5)->create();
       Place::factory(6)->create();
       Appointment::factory(10)->create();
    }
}
