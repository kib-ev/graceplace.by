<?php

namespace App\Models;

use App\Traits\HasComments;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasComments;

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'datetime',
        'canceled_at' => 'datetime',
        'duration' => 'integer'
    ];

//    protected static function boot() // редактирование не работает при этом
//    {
//        parent::boot();
//
//        static::addGlobalScope('canceled', function (\Illuminate\Database\Eloquent\Builder $builder) {
//            $builder->where('canceled_at', null);
//        });
//    }

    public function scopeWithoutCanceled(\Illuminate\Database\Eloquent\Builder $builder)
    {
        return $builder->whereNull('canceled_at');
    }

    public function scopeOnlyActive(\Illuminate\Database\Eloquent\Builder $builder)
    {
        return $builder->whereNull('canceled_at');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function master()
    {
        return $this->belongsTo(Master::class);
    }

    public function place()
    {
        return $this->belongsTo(Place::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function isSelfAdded(): bool
    {
        if(isset($this->user) && isset($this->master)) {
            return $this->user->phone == $this->master->getPhoneNumber();
        } else {
            return false;
        }
    }

    public function mergeWithClosest()
    {
        $thisDayAppointments = Appointment::where('master_id', $this->master_id)
            ->where('place_id', $this->place_id)
            ->whereDate('date', $this->date)
            ->where('id', '!=', $this->id)
            ->get();

        foreach ($thisDayAppointments as $thisDayAppointment) {
            $this->mergeAppointment($thisDayAppointment);
        }
    }

    public function mergeAppointment(Appointment $appointment)
    {
        // TODO add force merge

        $startTime = Carbon::parse($this->date);
        $endTime = Carbon::parse($this->date->clone()->addMinutes($this->duration));

        $appStartTime = Carbon::parse($appointment->date);
        $appEndTime = Carbon::parse($appointment->date->clone()->addMinutes($appointment->duration));

        if ($startTime->between($appStartTime, $appEndTime) || $endTime->between($appStartTime, $appEndTime)) {

            $this->update([
                'date' => $startTime->lessThan($appStartTime) ? $startTime : $appStartTime,
                'duration' => $this->duration + $appointment->duration - $appEndTime->diffInMinutes($startTime)
            ]);

            $appointment->delete();
        }

        // TODO MERGE DESCRIPTION IF NOT NULL
    }

    public static function contains(Appointment $haystack, Appointment $needle)
    {
        $startTime = Carbon::parse($haystack->date);
        $endTime = Carbon::parse($haystack->date->clone()->addMinutes($haystack->duration));

        $appStartTime = Carbon::parse($needle->date);
        $appEndTime = Carbon::parse($needle->date->clone()->addMinutes($needle->duration));

        return $appEndTime >= $startTime && $appEndTime <= $endTime && $appStartTime >= $startTime && $appStartTime <= $endTime;
    }

    public function isOverlay($masterId = null): bool
    {
        $startTime = Carbon::parse($this->date);
        $endTime = Carbon::parse($this->date->clone()->addMinutes($this->duration));

        $placeId = $this->place_id;

        $appointments = Appointment::onlyActive()->when(isset($placeId), function (Builder $builder) use ($placeId) {
            $builder->where('place_id', $placeId);
        })->when(isset($masterId), function (Builder $builder) use ($masterId) {
            $builder->where('master_id', $masterId);
        })->when(isset($this->id), function (Builder $builder) {
            $builder->where('id', '!=',  $this->id);
        })->whereDate('date', $this->date)->get();

        if(count($appointments) > 0 && $this->full_day || count($appointments->where('full_day'))) {
            return true;
        }

        foreach ($appointments as $appointment) {

            $appStartTime = Carbon::parse($appointment->date);
            $appEndTime = Carbon::parse($appointment->date->clone()->addMinutes($appointment->duration));

            if ($startTime->between($appStartTime, $appEndTime, false) || $endTime->between($appStartTime, $appEndTime, false)) {
                return true;
            }
        }

        return false;
    }

    public function getExpectedPrice(): float
    {
        return round($this->place->price_hour * $this->duration / 60, 2);
    }
}
