<?php

namespace App\Models;

use App\Services\AppointmentService;
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
        'start_at' => 'datetime',
        'canceled_at' => 'datetime',
        'duration' => 'integer'
    ];

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
            ->whereDate('start_at', $this->start_at)
            ->where('id', '!=', $this->id)
            ->get();

        foreach ($thisDayAppointments as $thisDayAppointment) {
            $this->mergeAppointment($thisDayAppointment);
        }
    }

    public function mergeAppointment(Appointment $appointment)
    {
        // TODO add force merge

        $startTime = Carbon::parse($this->start_at);
        $endTime = Carbon::parse($this->start_at->clone()->addMinutes($this->duration));

        $appStartTime = Carbon::parse($appointment->start_at);
        $appEndTime = Carbon::parse($appointment->start_at->clone()->addMinutes($appointment->duration));

        if ($startTime->between($appStartTime, $appEndTime) || $endTime->between($appStartTime, $appEndTime)) {

            $this->update([
                'start_at' => $startTime->lessThan($appStartTime) ? $startTime : $appStartTime,
                'duration' => $this->duration + $appointment->duration - $appEndTime->diffInMinutes($startTime)
            ]);

            $appointment->delete();
        }

        // TODO MERGE DESCRIPTION IF NOT NULL
    }

    public static function contains(Appointment $haystack, Appointment $needle)
    {
        $startTime = Carbon::parse($haystack->datstart_ate);
        $endTime = Carbon::parse($haystack->start_at->clone()->addMinutes($haystack->duration));

        $appStartTime = Carbon::parse($needle->start_at);
        $appEndTime = Carbon::parse($needle->start_at->clone()->addMinutes($needle->duration));

        return $appEndTime >= $startTime && $appEndTime <= $endTime && $appStartTime >= $startTime && $appStartTime <= $endTime;
    }

    public function isOverlay($masterId = null): bool
    {
        $startTime = Carbon::parse($this->start_at);
        $endTime = Carbon::parse($this->start_at->clone()->addMinutes($this->duration));

        $placeId = $this->place_id;

        $appointments = Appointment::onlyActive()->when(isset($placeId), function (Builder $builder) use ($placeId) {
            $builder->where('place_id', $placeId);
        })->when(isset($masterId), function (Builder $builder) use ($masterId) {
            $builder->where('master_id', $masterId);
        })->when(isset($this->id), function (Builder $builder) {
            $builder->where('id', '!=',  $this->id);
        })->whereDate('start_at', $this->start_at)->get();

        if(count($appointments) > 0 && $this->is_full_day || count($appointments->where('is_full_day'))) {
            return true;
        }

        foreach ($appointments as $appointment) {

            $appStartTime = Carbon::parse($appointment->start_at);
            $appEndTime = Carbon::parse($appointment->start_at->clone()->addMinutes($appointment->duration));

            if ($startTime->between($appStartTime, $appEndTime, false) || $endTime->between($appStartTime, $appEndTime, false)) {
                return true;
            }
        }

        return false;
    }

    public function getExpectedPrice(): float
    {
        return (new AppointmentService())->calculateAppointmentCost($this);
    }
}
