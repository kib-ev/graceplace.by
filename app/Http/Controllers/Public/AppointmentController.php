<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function store(Request $request)
    {
        $appointment = Appointment::make();
        $appointment->fill($request->all());
        $appointment->date = Carbon::parse($request->get('datetime'));

        $appointment->user_id = auth()->id();
        $appointment->save();

        if($appointment->id && $request->get('comment')) {
            $appointment->addComment(Auth::user(), $request->get('comment'), USER_COMMENT_TYPE);
        }

        if($appointment->id) {
            return back();
        } else {
            return back()->withErrors('Ошибка сохранения.');;
        }

    }
}
