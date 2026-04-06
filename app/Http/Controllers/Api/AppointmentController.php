<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AppointmentStoreRequest;
use App\Models\Appointment;
use App\Models\Master;
use App\Models\Place;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppointmentController extends Controller
{
    public function store(AppointmentStoreRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $appointment = Appointment::create($request->validated());

            DB::commit();

            return response()->json([
                'status' => 'ok',
                'data' => [
                    'id' => $appointment->id,
                    'message' => 'Запись успешно создана'
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create appointment: ' . $e->getMessage(), [
                'data' => $request->validated(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Не удалось создать запись'
            ], 500);
        }
    }

    public function freeSlots(Request $request, Place $place = null): JsonResponse
    {
        $validator = validator($request->all(), [
            'date' => 'required|date_format:Y-m-d',
            'master_id' => 'required|exists:masters,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $master = Master::findOrFail($request->master_id);
            $slots = $place 
                ? $place->getFreeSlotsForMaster($master, $request->date)
                : $master->getFreeSlotsForDate($request->date);

            return response()->json([
                'status' => 'ok',
                'data' => $slots
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get free slots: ' . $e->getMessage(), [
                'data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Не удалось получить свободные слоты'
            ], 500);
        }
    }

    public function placesList(): JsonResponse
    {
        try {
            $places = Place::select(['id', 'name'])->get();
            
            return response()->json([
                'status' => 'ok',
                'data' => $places
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get places list: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Не удалось получить список мест'
            ], 500);
        }
    }

    public function placesListWithSlots(Request $request): JsonResponse
    {
        $validator = validator($request->all(), [
            'date' => 'required|date_format:Y-m-d',
            'master_id' => 'required|exists:masters,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $master = Master::findOrFail($request->master_id);
            $places = Place::all()->map(function ($place) use ($master, $request) {
                return [
                    'id' => $place->id,
                    'name' => $place->name,
                    'slots' => $place->getFreeSlotsForMaster($master, $request->date)
                ];
            });

            return response()->json([
                'status' => 'ok',
                'data' => $places
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get places with slots: ' . $e->getMessage(), [
                'data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Не удалось получить список мест со слотами'
            ], 500);
        }
    }

    public function mastersList(): JsonResponse
    {
        try {
            $masters = Master::select(['id', 'user_id', 'first_name', 'last_name', 'patronymic'])
                ->get()
                ->map(function ($master) {
                    return [
                        'id' => $master->id,
                        'name' => $master->full_name
                    ];
                });

            return response()->json([
                'status' => 'ok',
                'data' => $masters
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get masters list: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Не удалось получить список мастеров'
            ], 500);
        }
    }

    /**
     * Получить все записи за выбранный промежуток времени.
     * GET /api/appointments?start_date=YYYY-MM-DD&end_date=YYYY-MM-DD
     */
    public function index(Request $request): JsonResponse
    {
        $validator = validator($request->all(), [
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $startDate = $request->input('start_date') . ' 00:00:00';
            $endDate = $request->input('end_date') . ' 23:59:59';

            $appointments = Appointment::with(['place:id,name', 'user:id,name,email'])
                ->whereBetween('start_at', [$startDate, $endDate])
                ->orderBy('start_at')
                ->get()
                ->map(function ($appointment) {
                    return [
                        'id' => $appointment->id,
                        'user_id' => $appointment->user_id,
                        'place_id' => $appointment->place_id,
                        'client_id' => $appointment->client_id,
                        'start_at' => $appointment->start_at->format('Y-m-d H:i:s'),
                        'duration' => $appointment->duration,
                        'end_at' => $appointment->end_at->format('Y-m-d H:i:s'),
                        'description' => $appointment->description,
                        'canceled_at' => $appointment->canceled_at?->format('Y-m-d H:i:s'),
                        'created_at' => $appointment->created_at->format('Y-m-d H:i:s'),
                        'place' => $appointment->place ? ['id' => $appointment->place->id, 'name' => $appointment->place->name] : null,
                        'user' => $appointment->user ? ['id' => $appointment->user->id, 'name' => $appointment->user->name, 'email' => $appointment->user->email] : null,
                    ];
                });

            return response()->json([
                'status' => 'ok',
                'data' => $appointments,
                'meta' => [
                    'start_date' => $request->input('start_date'),
                    'end_date' => $request->input('end_date'),
                    'total' => $appointments->count(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get appointments by date range: ' . $e->getMessage(), [
                'data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Не удалось получить список записей'
            ], 500);
        }
    }
}
