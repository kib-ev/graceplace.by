<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\StorageBooking;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class StorageBookingController extends Controller
{
    public function index()
    {
        $storageBookings = StorageBooking::all();

        return view('admin.storage-bookings.index', compact('storageBookings'));
    }

    public function show(StorageBooking $storageBooking)
    {
        return redirect()->route('admin.storage-bookings.edit', $storageBooking);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        // Активна только одна запись на ячейку — завершаем все остальные
        StorageBooking::where('model_id', $data['model_id'])
            ->where('model_class', $data['model_class'])
            ->whereNull('finished_at')
            ->update(['finished_at' => now()]);

        $storageBooking = new StorageBooking($data);
        $storageBooking->save();

        $storageBooking->load('cell');
        $amount = ($storageBooking->cell->cost_per_month ?? 0) * ($storageBooking->duration / 30);
        if ($amount > 0) {
            (new PaymentService())->createPaymentRequirement(
                $storageBooking,
                $amount,
                $storageBooking->duration,
                $storageBooking->start_at
            );
        }

        return redirect()->back();
    }

    public function edit(StorageBooking $storageBooking)
    {
        $storageBooking->load(['paymentRequirements.user.master', 'payments.user.master', 'user.master', 'cell']);
        return view('admin.storage-bookings.edit', compact('storageBooking'));
    }

    public function update(Request $request, StorageBooking $storageBooking)
    {
        $data = $request->all();

        if (isset($data['activate'])) {
            StorageBooking::where('model_id', $storageBooking->model_id)
                ->where('model_class', $storageBooking->model_class)
                ->whereNull('finished_at')
                ->update(['finished_at' => now()]);
            $storageBooking->update(['finished_at' => null]);
            return redirect()->route('admin.storage-bookings.edit', $storageBooking)->with('success', 'Бронь активирована.');
        }

        if (isset($data['extend'])) {
            // Продление = новая запись на 30 дней (вместо +30 к текущей)
            $amount = $storageBooking->cell->cost_per_month;
            $newStartAt = $storageBooking->start_at->copy()->addDays($storageBooking->duration);

            $storageBooking->update(['finished_at' => now()]);

            $newBooking = StorageBooking::create([
                'user_id' => $storageBooking->user_id,
                'model_class' => $storageBooking->model_class,
                'model_id' => $storageBooking->model_id,
                'start_at' => $newStartAt,
                'duration' => 30,
            ]);

            $paymentService = new PaymentService();
            $paymentService->createPaymentRequirement($newBooking, $amount, 30, $newStartAt);
            $payment = $paymentService->createPayment($newBooking, $amount, Payment::METHOD_CASH);
            $paymentService->changePaymentStatus($payment, Payment::STATUS_COMPLETED);

            return redirect()->route('admin.storage-bookings.edit', $newBooking)
                ->with('success', 'Создана новая аренда на 30 дней.');
        }

        $storageBooking->fill($data);
        $storageBooking->save();

        return back();
    }

    public function destroy(StorageBooking $storageBooking)
    {
        $storageBooking->delete();
        return redirect()->back();
    }

    public function payments(StorageBooking $storage_booking)
    {
        return redirect()->route('admin.storage-bookings.edit', $storage_booking)->withFragment('payments');
    }
}
