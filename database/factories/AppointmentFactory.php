<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Master;
use App\Models\Place;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'master_id' => Master::get()->random()->id,
            'place_id' => Place::get()->random()->id,
            'client_id' => null,
        ];
    }
}
