<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Master;
use App\Models\Place;
use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (is_null($request->get('date_from')) || is_null($request->get('date_from'))) {
            $parameters = [
                'date_from' => now()->format('Y-m-d'),
                'date_to' => now()->addWeek()->endOfWeek()->format('Y-m-d'),
            ];

            return redirect()->route('admin.appointments.index', $parameters);
        }

        $appointments = \App\Models\Appointment::orderBy('start_at');

        // DATE_FROM DATE_TO
        $dateFrom = Carbon::parse($request->get('date_from'));
        $dateTo = Carbon::parse($request->get('date_to'))->endOfDay();

        $appointments->when($request->get('date_from'), function ($query) use ($request, $dateFrom) {
            $query->where('start_at', '>=', $dateFrom);
        })->when($request->get('date_to'), function ($query) use ($request, $dateTo) {
            $query->where('start_at', '<=', $dateTo);
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
            $query->whereDate('start_at', $request->get('date'));
        });

        $appointments = $appointments->with(['user.master', 'place', 'paymentRequirements', 'payments', 'comments.user'])->get();

        $filterPlace  = $request->has('place_id')  ? Place::find($request->get('place_id'))  : null;
        $filterMaster = $request->has('master_id') ? Master::find($request->get('master_id')) : null;

        return view('admin.appointments.index', compact('appointments', 'dateFrom', 'dateTo', 'filterPlace', 'filterMaster'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $places = Place::all()->sortBy('name');
        return view('admin.appointments.create', ['appointment' => null, 'places' => $places]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $appointment = Appointment::make();
        $appointment->fill($request->all());
        $appointment->start_at = Carbon::parse($request->get('date') . ' ' . $request->get('time'));
        $appointment->save();

        if ($appointment->id) {
            return redirect()->route('admin.appointments.edit', $appointment);
        } else {
            return back()->withErrors('Ошибка сохранения.');;
        }
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
        $appointment->load('paymentRequirements', 'payments');
        $places = Place::all()->sortBy('name');
        return view('admin.appointments.edit', compact('appointment', 'places'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Appointment $appointment)
    {
        // CANCEL with penalties
        if ($request->get('cancel') == 1) {
            $penalty = $request->get('cancel_penalty');

            $allowed = ['penalty_50', 'penalty_100', 'penalty_custom', 'default'];
            $penaltyOverride = in_array($penalty, $allowed, true) ? $penalty : null;

            $penaltyCustomAmount = null;
            if ($penaltyOverride === \App\Models\PaymentRequirement::REASON_PENALTY_CUSTOM) {
                $penaltyCustomAmount = (float) $request->input('cancel_penalty_amount', 0);
                if ($penaltyCustomAmount <= 0 || $penaltyCustomAmount > 999999.99) {
                    return back()->withErrors('Сумма штрафа должна быть от 0.01 до 999999.99 BYN.');
                }
            }

            try {
                (new AppointmentService())->cancelAppointment(
                    user: auth()->user(),
                    appointment: $appointment,
                    cancellationReason: $request->get('cancellation_reason'),
                    penaltyOverride: $penaltyOverride,
                    penaltyCustomAmount: $penaltyCustomAmount,
                );
            } catch (\Exception $e) {
                return back()->withErrors($e->getMessage());
            }

            return back();
        }

        // UPDATE
        $appointment->fill($request->all());
        if($request->has('date') && $request->has('time')) {
            $appointment->start_at = Carbon::parse(time: $request->get('date') . ' ' . $request->get('time'));
        }

        if ($appointment->save()) {
            return back();
        } else {
            return back()->withErrors('Ошибка сохранения.');;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
        return redirect()->route('admin.appointments.index');
    }

    public function payments(Appointment $appointment)
    {
        $appointment->load('paymentRequirements', 'payments');
        return view('admin.appointments.payments', compact('appointment'));
    }

    public function mergeClosestAppointments(Request $request)
    {
        $date = $request->date;

        $appointments = Appointment::whereDate('start_at', Carbon::parse($date))
            ->whereNull('canceled_at')
            ->with(['place', 'paymentRequirements'])
            ->get();

        (new AppointmentService())->mergeAppointments($appointments, AppointmentService::MERGE_GAP_MINUTES);

        return redirect()->route('admin.appointments.index', ['date_from'=> $date, 'date_to' => $date]);
    }

    public function createRequirementsForDate(Request $request)
    {
        $date = Carbon::parse($request->get('date'));

        $appointments = Appointment::query()
            ->whereDate('start_at', $date)
            ->whereNull('canceled_at')
            ->with('paymentRequirements')
            ->get();

        foreach ($appointments as $appointment) {
            if ($appointment->paymentRequirements->count() === 0) {
                $expected = $appointment->getExpectedPrice();
                $appointment->createRequirement($expected, $appointment->start_at->toDateString());
            }
        }

        return redirect()->route('admin.appointments.index', ['date_from'=> $date->toDateString(), 'date_to' => $date->toDateString()])->with('success', 'Требования созданы');
    }
}
