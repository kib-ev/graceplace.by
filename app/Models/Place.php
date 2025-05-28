<?php

namespace App\Models;

use App\Services\AppointmentService;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function bookings()
    {
        return $this->appointments();
    }

    public function isAppointment(Carbon $date) : Appointment|null
    {
        $isAppointment = null;

        foreach($this->appointments()->whereNull('canceled_at')->whereDay('start_at', $date)->get() as $appointment) {
            if($date->greaterThanOrEqualTo($appointment->start_at) && $date->lessThan($appointment->start_at->addMinutes($appointment->duration))) {
                $isAppointment = $appointment;
            }
        }

        return $isAppointment;
    }

    public function isFree(Carbon $date): bool
    {
         return !$this->isAppointment($date);
    }

    public function nextAppointment(Carbon $date) : ?Appointment
    {
        return $this->appointments()
            ->whereNull('canceled_at')
            ->whereDate('start_at', $date)
            ->where('start_at', '>=', $date)
            ->orderBy('start_at')
            ->first();
    }

    public function nextAppointmentToMinutes(Carbon $date) //: ?CarbonInterval
    {
        $appointment = $this->nextAppointment($date);

        if($appointment) {
            return CarbonInterval::minutes($date->diffInMinutes($appointment->start_at))->totalMinutes;
        }

        return null;
    }

    public function isFullDayBusy(Carbon $date): Appointment|null
    {
        return $this->appointments()
            ->whereNull('canceled_at')
            ->whereDate('start_at', $date)
            ->where('is_full_day', 1)
            ->first();
    }

    public function getHourlyCost()
    {
        return $this->price_per_hour;
    }

    // Метод для проверки доступности рабочего места на конкретную дату
    public function isAvailableOnDate($date): bool
    {
        $appointments = $this->appointments()->whereDate('start_at', $date)->get();

        // Если нет бронирований на этот день, место доступно
        if ($appointments->isEmpty()) {
            return true;
        }

        return false;
    }

    // Метод для получения списка свободных интервалов на определённую дату
//    public function availableTimesOnDate($date): array
//    {
//        $appointments = $this->appointments()->whereDate('start_at', $date)->get();
//
//        // Логика для вычисления свободных временных интервалов
//        // Предполагается, что рабочее время с 9:00 до 21:00
//        $availableTimes = [
//            ['start_time' => '09:00', 'end_time' => '21:00', 'status' => 'available']
//        ];
//
//        foreach ($appointments as $appointment) {
//
//            $startAt = $appointment->start_at;
//            $endAt = $appointment->start_at->addMinutes($appointment->duration);
//
//            // Вычитание забронированного интервала из доступного времени
//            // Здесь вам нужно учесть временные интервалы бронирования
//            // и удалять занятые интервалы из массива $availableTimes
//        }
//
//        return $availableTimes;
//    }

    // Метод для получения списка свободных интервалов на определённую дату
    public function availableTimesOnDate($date)
    {
        // Рабочее время с 9:00 до 21:00
        $openingTime = Carbon::createFromTime(7, 0, 0);
        $closingTime = Carbon::createFromTime(23, 30, 0);

        // Начальные доступные интервалы (весь рабочий день)
        $availableTimes = [
            [
                'start_time' => $openingTime->format('H:i'),
                'end_time' => $closingTime->format('H:i'),
                'status' => 'available'
            ]
        ];

        // Получаем все бронирования на эту дату
        $bookings = $this->bookings()->whereDate('start_at', $date)->get();

        // Пройдем по каждому бронированию и вычтем занятые интервалы, включая перерывы
        foreach ($bookings as $booking) {
            $bookingStart = Carbon::parse($booking->start_at);
            $bookingEnd = $bookingStart->copy()->addMinutes($booking->duration);

            // Добавляем 30 минут перерыва до и после бронирования
            $bookingStartWithBreak = $bookingStart->copy()->subMinutes(30);  // 30 минут до бронирования
            $bookingEndWithBreak = $bookingEnd->copy()->addMinutes(30);      // 30 минут после бронирования

            $newAvailableTimes = [];

            foreach ($availableTimes as $availableTime) {
                $availableStart = Carbon::createFromTimeString($availableTime['start_time']);
                $availableEnd = Carbon::createFromTimeString($availableTime['end_time']);

                // Если бронирование с перерывами полностью за пределами доступного интервала, его не трогаем
                if ($bookingEndWithBreak <= $availableStart || $bookingStartWithBreak >= $availableEnd) {
                    $newAvailableTimes[] = $availableTime;
                    continue;
                }

                // Если начало бронирования с перерывом позже начала доступного времени, создаем новый доступный интервал до бронирования
                if ($bookingStartWithBreak > $availableStart) {
                    $newAvailableTimes[] = [
                        'start_time' => $availableStart->format('H:i'),
                        'end_time' => $bookingStartWithBreak->format('H:i'),
                        'status' => 'available'
                    ];
                }

                // Если конец бронирования с перерывом раньше конца доступного времени, создаем новый доступный интервал после бронирования
                if ($bookingEndWithBreak < $availableEnd) {
                    $newAvailableTimes[] = [
                        'start_time' => $bookingEndWithBreak->format('H:i'),
                        'end_time' => $availableEnd->format('H:i'),
                        'status' => 'available'
                    ];
                }
            }

            // Обновляем список доступных интервалов после каждого бронирования
            $availableTimes = $newAvailableTimes;
        }

        return $this->excludeShortTimes($availableTimes);
    }

    public function excludeShortTimes($availableTimes): array
    {
        $filteredTimes = [];

        foreach ($availableTimes as $time) {
            $startTime = Carbon::createFromTimeString($time['start_time']);
            $endTime = Carbon::createFromTimeString($time['end_time']);

            // Рассчитываем продолжительность интервала в минутах
            $duration = $startTime->diffInMinutes($endTime);

            // Если продолжительность больше 30 минут, добавляем в отфильтрованный массив
            if ($duration > 30) {
                $filteredTimes[] = $time;
            }
        }

        return $filteredTimes;
    }

    // Проверка доступности всех рабочих мест за период
    public function availableTimesOnDateRange($startDate, $endDate)
    {
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        $availableTimes = [];

        for ($date = $startDate->clone(); $date->lessThanOrEqualTo($endDate); $date->addDay()) {
            $availableTimes = array_merge($availableTimes, $this->availableTimesOnDate($date));
        }

        return $availableTimes;
    }

    public function getAverageProfitPerMonth($lastMonths = 3): float
    {
        $appointments = $this->appointments()
            ->whereNull('canceled_at')
            ->whereBetween('start_at', [now()->subDays($lastMonths  * 30), now()])
            ->get();

        return $appointments->sum(function ($a) { return $a->price; }) / $lastMonths;
    }

    public function getAverageRentHoursPerDay($lastMonths = 3): string
    {
        $appointments = $this->appointments()
            ->whereNull('canceled_at')
            ->whereBetween('start_at', [now()->subDays($lastMonths  * 30), now()])
            ->get();

        return number_format($appointments->sum(function ($a) { return $a->duration / 60; }) / ($lastMonths  * 30), 2, '.');
    }

}
