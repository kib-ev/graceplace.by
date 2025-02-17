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
            'amount' => 'required|numeric|min:0.01',
            'expiration_days' => 'required|int',
        ]);

        /** @var Appointment $appointment */
        $appointment = Appointment::find($request->appointment_id);

        (new PaymentService())->createPaymentRequirement($appointment, $request->amount, $request->expiration_days, $appointment->created_at);

        return redirect()->route('admin.appointments.payments.show', $request->appointment_id)->with('success', 'Требование на оплату создано.');
    }

    public function destroy($requirementId)
    {
        $requirement = PaymentRequirement::find($requirementId);
        $requirement->delete();
        return back();
    }
}
