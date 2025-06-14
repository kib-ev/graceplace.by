<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSchedule extends Model
{
    protected $fillable = [
        'user_id',
        'work_date',
        'start_time',
        'end_time'
    ];

    protected $casts = [
        'work_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime'
    ];

    public function getStartTimeAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('H:i') : null;
    }

    public function getEndTimeAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('H:i') : null;
    }

    public function setStartTimeAttribute($value)
    {
        $this->attributes['start_time'] = $value ? Carbon::parse($value)->format('H:i:s') : null;
    }

    public function setEndTimeAttribute($value)
    {
        $this->attributes['end_time'] = $value ? Carbon::parse($value)->format('H:i:s') : null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isWorkingAt(Carbon $dateTime)
    {
        $date = $dateTime->format('Y-m-d');
        $time = $dateTime->format('H:i:s');

        return $this->where('work_date', $date)
            ->where('start_time', '<=', $time)
            ->where('end_time', '>=', $time)
            ->exists();
    }

    public static function getScheduleForDateRange($userId, $startDate, $endDate)
    {
        $schedules = self::where('user_id', $userId)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->get();

        $result = [];
        foreach ($schedules as $schedule) {
            $date = $schedule->work_date->format('Y-m-d');
            if (!isset($result[$date])) {
                $result[$date] = [];
            }
            $result[$date][] = [
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time
            ];
        }

        return $result;
    }
} 