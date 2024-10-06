<?php

namespace App\Services;

use App\Models\StorageBooking;
use Carbon\Carbon;

class StorageBookingService
{
    /**
     * Рассчитывает стоимость бронирования ячейки и создает запись о бронировании.
     *
     * @param StorageCell $cell
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return StorageBooking
     */
//    public function bookStorageCell(StorageCell $cell, Carbon $startDate, Carbon $endDate): StorageBooking
//    {
//        // Проверяем, доступна ли ячейка
//        if ($cell->status !== 'available') {
//            throw new \Exception('Storage cell is not available for booking');
//        }
//
//        // Рассчитываем количество дней аренды
//        $days = $startDate->diffInDays($endDate);
//
//        // Рассчитываем общую стоимость
//        $totalCost = $cell->price_per_day * $days;
//
//        // Создаем бронирование
//        $booking = new StorageBooking();
//        $booking->storage_cell_id = $cell->id;
//        $booking->user_id = auth()->user()->id; // Текущий авторизованный пользователь
//        $booking->start_date = $startDate;
//        $booking->end_date = $endDate;
//        $booking->total_cost = $totalCost;
//        $booking->status = 'active';
//        $booking->save();
//
//        // Обновляем статус ячейки
//        $cell->status = 'booked';
//        $cell->save();
//
//        return $booking;
//    }

    /**
     * Продлевает бронирование ячейки хранения.
     *
     * @param StorageBooking $booking
     * @param int $additionalDays
     * @return StorageBooking
     */
    public function extendBooking(StorageBooking $booking, int $additionalDays): StorageBooking
    {
//        $now = Carbon::now();
//
//        // Если бронирование просрочено, включаем просроченные дни в расчет
//        if ($booking->status == 'overdue') {
//            $overdueDays = $now->diffInDays($booking->end_date);
//            $overdueCost = $overdueDays * $this->getOverdueRate($booking->storageCell);
//
//            // Добавляем сумму за просрочку к общему счету
//            $booking->total_cost += $overdueCost;
//        }
//
//        // Продлеваем бронирование на дополнительные дни
//        $booking->end_date = Carbon::parse($booking->end_date)->addDays($additionalDays);
//
//        // Рассчитываем стоимость за продление
//        $additionalCost = $additionalDays * $this->getDailyRate($booking->storageCell);
//        $booking->total_cost += $additionalCost;
//
//        // Обновляем статус на active, если было продление после просрочки
//        $booking->status = 'active';
//        $booking->overdue_days = 0;
//        $booking->overdue_cost = 0;
//
//        $booking->save();

        $booking->duration->addDays($additionalDays);
        $booking->save();

        return $booking;
    }

    /**
     * Возвращает ставку аренды за день для ячейки.
     *
     * @param StorageCell $cell
     * @return float
     */
    protected function getDailyRate($cell): float
    {
        return $cell->price_per_day;
    }

    /**
     * Возвращает ставку за просроченный день аренды.
     *
     * @param StorageCell $cell
     * @return float
     */
    protected function getOverdueRate($cell): float
    {
        return $cell->price_per_day * 1.2; // 120% от обычной ставки
    }
}
