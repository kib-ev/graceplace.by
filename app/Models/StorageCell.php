<?php

namespace App\Models;

use App\Traits\Rentable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StorageCell extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ['id'];

    public function bookings (): HasMany
    {
        return $this->hasMany(StorageBooking::class, 'model_id', 'id')
            ->where('model_class', $this->getMorphClass())
            ->orderBy('created_at');
    }

    public function addBooking(User $user, Carbon $startAt, int $durationMinutes = null)
    {
        $booking = StorageBooking::make([
            'user_id' => $user->id,
            'model_class' => $this->getMorphClass(),
            'model_id' => $this->id,
            'start_at' => $startAt,
            'duration' => $durationMinutes,
        ]);

        $this->bookings()->save($booking);
    }

}
