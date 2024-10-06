<?php

namespace App\Http\Controllers;

use App\Models\StorageBooking;
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
        if (auth()->user()) {
            $data['user_id'] = auth()->id();
        }
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
        $storageBooking->fill($request->all());
        $storageBooking->save();

        return back();
    }

    public function destroy(StorageBooking $storageBooking)
    {
        $storageBooking->delete();
        return redirect()->back();
    }
}
