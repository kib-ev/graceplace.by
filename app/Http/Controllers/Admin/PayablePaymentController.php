<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\StorageBooking;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PayablePaymentController extends Controller
{
    private const ALLOWED_TYPES = [
        Appointment::class,
        StorageBooking::class,
    ];

    public function show(Request $request)
    {
        $request->validate([
            'payable_type' => 'required|string|in:' . implode(',', self::ALLOWED_TYPES),
            'payable_id' => 'required|integer',
        ]);

        $payable = $this->resolvePayable($request);
        $payable->load(['paymentRequirements.user.master', 'payments.user.master', 'user.master']);

        if ($payable instanceof Appointment) {
            $payable->load('place');
        } elseif ($payable instanceof StorageBooking) {
            $payable->load('cell');
        }

        return view('admin.payments.manage', compact('payable'));
    }

    public function storeRequirement(Request $request)
    {
        $payable = $this->resolvePayable($request);

        $request->validate([
            'payable_type' => 'required|string|in:' . implode(',', self::ALLOWED_TYPES),
            'payable_id' => 'required|integer',
            'amount' => 'required|numeric|min:0',
            'expiration_days' => 'nullable|integer|min:0',
        ]);

        $expirationDays = $request->expiration_days ?? 30;
        $dateTime = $request->created_at ? Carbon::parse($request->created_at) : $payable->start_at;

        (new PaymentService())->createPaymentRequirement(
            $payable,
            (float) $request->amount,
            $expirationDays,
            $dateTime
        );

        return $this->redirectToManage($payable)->with('success', 'Требование на оплату создано.');
    }

    public function storePayment(Request $request)
    {
        $payable = $this->resolvePayable($request);

        $request->validate([
            'payable_type' => 'required|string|in:' . implode(',', self::ALLOWED_TYPES),
            'payable_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,service,card,bonus,other',
            'created_at' => 'required|string|date',
            'note' => 'nullable|string|max:1000',
        ]);

        $payable->load('paymentRequirements');
        $maxAmount = $payable->leftToPay();

        if ($payable->paymentRequirements->isEmpty() || $maxAmount <= 0) {
            return $this->redirectToManage($payable)
                ->withErrors(['amount' => 'Платеж можно создать только при наличии незакрытых платежных требований.']);
        }

        if ((float) $request->amount > $maxAmount) {
            return $this->redirectToManage($payable)
                ->withErrors(['amount' => 'Сумма платежа не может превышать остаток к оплате (' . number_format($maxAmount, 2, '.', '') . ' BYN).']);
        }

        $paymentService = new PaymentService();
        $payment = $paymentService->createPayment(
            $payable,
            (float) $request->amount,
            $request->payment_method,
            Carbon::parse($request->created_at),
            $request->note
        );
        $paymentService->changePaymentStatus($payment, Payment::STATUS_COMPLETED);

        return $this->redirectToManage($payable)->with('success', 'Платеж успешно создан.');
    }

    private function resolvePayable(Request $request): object
    {
        $type = $request->get('payable_type');
        $id = $request->get('payable_id');

        if (!in_array($type, self::ALLOWED_TYPES)) {
            abort(404, 'Неподдерживаемый тип: ' . $type);
        }

        $model = $type::find($id);
        if (!$model) {
            abort(404, 'Запись не найдена.');
        }

        return $model;
    }

    private function redirectToManage(object $payable)
    {
        return redirect()->route('admin.payments.manage', [
            'payable_type' => $payable::class,
            'payable_id' => $payable->id,
        ])->withFragment('payments');
    }
}
