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

    public function store(Request $request)
    {
        $data = $request->all();
        $storageBooking = new StorageBooking($data);
        $storageBooking->save();
        return redirect()->back();
    }

    public function edit(StorageBooking $storageBooking)
    {
        return view('admin.storage-bookings.edit', compact('storageBooking'));
    }

    public function update(Request $request, StorageBooking $storageBooking)
    {
        $data = $request->all();
        $amount = $storageBooking->cell->cost_per_month;

        $useBalance = $request->get('use_balance') == 'on';

        // ***********************
        // TODO REFACTOR
        if (isset($data['extend']) && isset($data['duration']) && $data['duration'] > $storageBooking->duration) {
            (new PaymentService())->payForStorageBooking($storageBooking, $storageBooking->cell->cost_per_month, $useBalance);

            (new PaymentService())->createPaymentRequirement($storageBooking, $amount, 30, $storageBooking->start_at);
            (new PaymentService())->createPayment($storageBooking, $amount, Payment::METHOD_CASH);
        }
        // ***********************

        $storageBooking->fill($data);
        $storageBooking->save();

        return back();
    }

    public function destroy(StorageBooking $storageBooking)
    {
        $storageBooking->delete();
        return redirect()->back();
    }

    public function payments(StorageBooking $appointment)
    {
        $appointment->load('paymentRequirements', 'payments');
        return view('admin.appointments.payments', compact('appointment'));
    }
}
