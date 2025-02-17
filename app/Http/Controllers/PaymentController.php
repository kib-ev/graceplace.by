<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Payment;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        return view('admin.payments.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'created_at' => 'required|string|date',
        ]);

        $appointment = Appointment::find($request->appointment_id);

        $amount = $request->get('amount');
        $paymentMethod = $request->get('payment_method');
        $createdAt = Carbon::parse($request->created_at);

        $payment = (new PaymentService())->createPayment($appointment, $amount, $paymentMethod, $createdAt);

//        if ($payment->payment_method == 'cash') {
            (new PaymentService())->changePaymentStatus($payment, Payment::STATUS_COMPLETED); // TODO REMOVE / temp
//        }

        return redirect()->route('admin.appointments.payments.show', $request->appointment_id)->with('success', 'Платеж успешно создан.');
    }

    public function updateStatus(Request $request, Payment $payment)
    {
//        $request->validate([
//            'status' => 'required|exists:appointments,id', // todo add validation
//        ]);

        (new PaymentService())->changePaymentStatus($payment, $request->status);

        return redirect()->back()->with('success', 'Статус успешно изменен.');
    }

    public function destroy(Payment $payment)
    {
        if ($payment->status == Payment::STATUS_CANCELLED) {
            $payment->delete();
        }

        return back();
    }
}
