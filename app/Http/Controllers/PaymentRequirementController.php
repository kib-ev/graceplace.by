<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\PaymentRequirement;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentRequirementController extends Controller
{
    // Сохранение нового требования
    /**
     * @throws \Exception
     */
    public function store(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'amount' => 'required|numeric|min:0',
            'expiration_days' => 'required|int',
        ]);

        /** @var Appointment $appointment */
        $appointment = Appointment::find($request->appointment_id);

        (new PaymentService())->createPaymentRequirement($appointment, $request->amount, $request->expiration_days, $appointment->created_at);

        return redirect()->back()->with('success', 'Требование на оплату создано.');
    }

    public function destroy($requirementId)
    {
        $requirement = PaymentRequirement::find($requirementId);
        
        if (!$requirement) {
            return back()->withErrors('Требование не найдено.');
        }
        
        $appointment = $requirement->payable;
        
        if (!$appointment) {
            $requirement->delete();
            return back()->with('success', 'Требование удалено.');
        }
        
        // Check if there are completed payments
        $completedPaymentsCount = $appointment->payments()
            ->where('status', \App\Models\Payment::STATUS_COMPLETED)
            ->count();
        
        if ($completedPaymentsCount > 0) {
            return back()->withErrors('Невозможно удалить требование: существует ' . $completedPaymentsCount . ' завершенных платежей. Сначала отмените все платежи.');
        }
        
        $requirement->delete();
        return back()->with('success', 'Требование удалено.');
    }
}
