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
            'note' => 'nullable|string|max:1000',
        ]);

        $appointment = Appointment::find($request->appointment_id);

        $amount = $request->get('amount');
        $paymentMethod = $request->get('payment_method');
        $createdAt = Carbon::parse($request->created_at);
        $note = $request->get('note');

        $payment = (new PaymentService())->createPayment($appointment, $amount, $paymentMethod, $createdAt, $note);

//        if ($payment->payment_method == 'cash') {
            (new PaymentService())->changePaymentStatus($payment, Payment::STATUS_COMPLETED); // TODO REMOVE / temp
//        }

        return redirect()->back()->with('success', 'Платеж успешно создан.');
    }

    public function updateStatus(Request $request, Payment $payment)
    {
        (new PaymentService())->changePaymentStatus($payment, $request->status);

        return redirect()->back()->with('success', 'Статус успешно изменен.');
    }

    public function updateMethod(Request $request, Payment $payment)
    {
        $request->validate([
            'payment_method' => 'required|string|in:cash,card,service,bonus,other',
        ]);

        $payment->update(['payment_method' => $request->payment_method]);

        return redirect()->back()->with('success', 'Метод оплаты изменен.');
    }

    public function update(Request $request, Payment $payment)
    {
        $request->validate([
            'payment_method' => 'required|string|in:cash,card,service,bonus,other',
            'status' => 'required|string|in:completed,refunded,pending,cancelled',
            'note' => 'nullable|string|max:1000',
        ]);

        $paymentService = new PaymentService();
        if ($payment->status !== $request->status) {
            $paymentService->changePaymentStatus($payment, $request->status);
        }

        $payment->update([
            'payment_method' => $request->payment_method,
            'note' => $request->note,
        ]);

        return redirect()->back()->with('success', 'Платеж сохранен.');
    }

    public function destroy(Payment $payment)
    {
        // Перед удалением отменяем (если completed — вернёт сумму в требования)
        if ($payment->status === Payment::STATUS_COMPLETED) {
            (new PaymentService())->changePaymentStatus($payment, Payment::STATUS_CANCELLED);
        }
        $payment->delete();
        return back()->with('success', 'Платеж удален.');
    }
}
