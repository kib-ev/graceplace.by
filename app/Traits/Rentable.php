<?php

namespace App\Traits;

use App\Models\Rent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait Rentable
{
    public function rents(): HasMany
    {
        return $this->hasMany(Rent::class, 'model_id', 'id')
            ->where('model_class', $this->getMorphClass())
            ->orderBy('created_at');
    }

    public function addRent(User $user, Carbon $startAt, int $durationMinutes = null)
    {
        $rent = Rent::make([
            'user_id' => $user->id,
            'model_class' => $this->getMorphClass(),
            'model_id' => $this->id,
            'start_at' => $startAt,
            'duration' => $durationMinutes,
        ]);

        $this->rents()->save($rent);
    }
}
