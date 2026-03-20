<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\StorageBooking;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StorageBookingPaymentController extends Controller
{
    public function storeRequirement(Request $request)
    {
        $request->validate([
            'storage_booking_id' => 'required|exists:storage_bookings,id',
            'amount' => 'required|numeric|min:0',
            'expiration_days' => 'nullable|integer|min:0',
        ]);

        $storageBooking = StorageBooking::findOrFail($request->storage_booking_id);
        $expirationDays = $request->expiration_days ?? 30;
        $dateTime = $request->created_at ? Carbon::parse($request->created_at) : $storageBooking->start_at;

        (new PaymentService())->createPaymentRequirement(
            $storageBooking,
            (float) $request->amount,
            $expirationDays,
            $dateTime
        );

        return redirect()->route('admin.storage-bookings.edit', $storageBooking)->withFragment('payments')->with('success', 'Требование на оплату создано.');
    }

    public function storePayment(Request $request)
    {
        $request->validate([
            'storage_booking_id' => 'required|exists:storage_bookings,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,service,card,bonus,other',
            'created_at' => 'required|string|date',
            'note' => 'nullable|string|max:1000',
        ]);

        $storageBooking = StorageBooking::findOrFail($request->storage_booking_id);
        $paymentService = new PaymentService();

        $payment = $paymentService->createPayment(
            $storageBooking,
            (float) $request->amount,
            $request->payment_method,
            Carbon::parse($request->created_at),
            $request->note
        );

        $paymentService->changePaymentStatus($payment, Payment::STATUS_COMPLETED);

        return redirect()->route('admin.storage-bookings.edit', $storageBooking)->withFragment('payments')->with('success', 'Платеж успешно создан.');
    }
}
