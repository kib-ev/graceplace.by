<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function store(Request $request)
    {
        $appointment = Appointment::make();
        $appointment->fill($request->all());
        $appointment->date = Carbon::parse($request->get('datetime'));

        $appointment->save();

        return back();
    }
}
