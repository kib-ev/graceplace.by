<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Place;
use App\Models\PlacePrice;
use App\Models\PaymentRequirement;
use App\Models\Appointment;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Create initial place prices from current places
        $places = Place::all();
        foreach ($places as $place) {
            if ($place->price_per_hour) {
                PlacePrice::create([
                    'place_id' => $place->id,
                    'price_per_hour' => $place->price_per_hour,
                    'effective_from' => $place->created_at ?? now(),
                ]);
            }
        }

        // Step 2: Update existing payment_requirements
        $requirements = PaymentRequirement::all();
        foreach ($requirements as $requirement) {
            // Set expected_amount and remaining_amount from amount_due if not set
            if (is_null($requirement->expected_amount)) {
                $requirement->expected_amount = $requirement->amount_due;
            }
            if (is_null($requirement->remaining_amount)) {
                $requirement->remaining_amount = $requirement->amount_due;
            }

            // Try to get price_per_hour_snapshot from appointment
            if (is_null($requirement->price_per_hour_snapshot) && $requirement->payable_type === 'App\\Models\\Appointment') {
                $appointment = Appointment::find($requirement->payable_id);
                if ($appointment && $appointment->place) {
                    $requirement->price_per_hour_snapshot = $appointment->place->getPriceForDate($appointment->start_at);
                }
            }

            $requirement->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove all place prices (cannot filter by note as it was removed)
        PlacePrice::truncate();
        
        // Reset payment_requirements fields
        PaymentRequirement::query()->update([
            'expected_amount' => 0,
            'remaining_amount' => 0,
            'price_per_hour_snapshot' => null,
        ]);
    }
};
