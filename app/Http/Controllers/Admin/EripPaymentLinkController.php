<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\EripPayment;
use App\Models\EripPaymentAllocation;
use App\Models\Payment;
use App\Models\StorageBooking;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EripPaymentLinkController extends Controller
{
    public function store(Request $request, PaymentService $paymentService)
    {
        $data = $request->validate([
            'erip_payment_id' => ['required', 'integer', 'exists:erip_payments,id'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
            'storage_booking_id' => ['nullable', 'integer', 'exists:storage_bookings,id'],
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'erip_date' => ['nullable', 'date'],
            'return_url' => ['nullable', 'string'],
        ]);

        $appointmentId = $data['appointment_id'] ?? null;
        $storageBookingId = $data['storage_booking_id'] ?? null;

        if (($appointmentId === null) === ($storageBookingId === null)) {
            throw ValidationException::withMessages([
                'appointment_id' => 'Укажите либо запись, либо бронь локера.',
            ]);
        }

        $eripPayment = EripPayment::query()->with('allocations')->findOrFail($data['erip_payment_id']);

        if ($appointmentId !== null) {
            $payable = Appointment::query()
                ->with('paymentRequirements')
                ->findOrFail($appointmentId);
        } else {
            $payable = StorageBooking::query()
                ->with('paymentRequirements')
                ->findOrFail($storageBookingId);
        }

        if ($payable->paymentRequirements->isEmpty()) {
            return back()->withErrors(['amount' => 'Нет платежных требований по выбранному объекту.']);
        }

        $leftToPay = $payable->leftToPay();
        if ($leftToPay <= 0) {
            return back()->withErrors(['amount' => 'По выбранному объекту нет долга.']);
        }

        $unallocated = $eripPayment->unallocated_amount;
        $requestedAmount = isset($data['amount']) ? round((float) $data['amount'], 2) : null;
        $amount = $requestedAmount !== null
            ? min($requestedAmount, $leftToPay, $unallocated)
            : min($leftToPay, $unallocated);

        $amount = round((float) $amount, 2);

        if ($amount <= 0) {
            return back()->withErrors(['amount' => 'Недостаточно остатка для привязки платежа.']);
        }

        DB::transaction(function () use ($paymentService, $payable, $eripPayment, $amount): void {
            $payment = $paymentService->createPayment(
                $payable,
                $amount,
                Payment::METHOD_SERVICE,
                $eripPayment->paid_at ?? now(),
                'ЕРИП: op# '.($eripPayment->operation_number ?? '—')
            );
            $paymentService->changePaymentStatus($payment, Payment::STATUS_COMPLETED);

            EripPaymentAllocation::create([
                'erip_payment_id' => $eripPayment->id,
                'payment_id' => $payment->id,
                'amount' => $amount,
                'created_by_user_id' => auth()->id(),
            ]);
        });

        $eripDate = $request->input('erip_date');
        $returnUrl = $request->input('return_url');

        if (is_string($returnUrl) && str_starts_with($returnUrl, url('/'))) {
            return redirect($returnUrl)->with('success', 'Платеж ЕРИП успешно привязан.');
        }

        return redirect()
            ->route('admin.erip-imports.index', array_filter([
                'erip_date' => $eripDate ?: now()->toDateString(),
            ]))
            ->with('success', 'Платеж ЕРИП успешно привязан.');
    }
}
