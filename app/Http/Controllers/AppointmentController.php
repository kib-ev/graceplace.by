<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if(is_null($request->get('date_from')) || is_null($request->get('date_from'))) {
            $parameters = [
                'date_from' => now()->format('Y-m-d'),
                'date_to' => now()->addWeek()->endOfWeek()->format('Y-m-d'),
            ];

            return redirect()->route('admin.appointments.index', $parameters);
        }

        $appointments = \App\Models\Appointment::orderBy('date');

        // DATE_FROM DATE_TO
        $dateFrom = Carbon::parse($request->get('date_from'));
        $dateTo = Carbon::parse($request->get('date_to'))->endOfDay();

        $appointments->when($request->get('date_from'), function ($query) use ($request, $dateFrom) {
            $query->where('date', '>=', $dateFrom);
        })->when($request->get('date_to'), function ($query) use ($request, $dateTo) {
            $query->where('date', '<=', $dateTo);
        });

        // PLACE
        $appointments->when($request->has('place_id'), function ($query) use ($request) {
            $query->where('place_id', $request->get('place_id'));
        });

        // MASTER
        $appointments->when($request->has('master_id'), function ($query) use ($request) {
            $query->where('master_id', $request->get('master_id'));
        });

        // SELECTED DATE
        $appointments->when($request->has('date'), function ($query) use ($request) {
            $query->whereDate('date', $request->get('date'));
        });

        $appointments = $appointments->get();

        return view('admin.appointments.index', compact('appointments', 'dateFrom', 'dateTo'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.appointments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $appointment = Appointment::make();
        $appointment->fill($request->all())->save();

        return redirect()->route('admin.appointments.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment)
    {
        return view('admin.appointments.show', compact('appointment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Appointment $appointment)
    {
        return view('admin.appointments.edit', compact('appointment'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Appointment $appointment)
    {
        // CANCEL
        if($request->get('cancel') == 1) {
            $appointment->update([
                'canceled_at' => now()
            ]);

            return back();
        }

        // UPDATE
        $appointment->fill($request->all())->save();

        return redirect()->route('admin.appointments.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
        return redirect()->route('admin.appointments.index');
    }
}
