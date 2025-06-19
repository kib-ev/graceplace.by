<?php

use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AppointmentController;
use Illuminate\Support\Facades\Validator;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['api_token'])->group(function() {
    // User routes
    Route::get('/user/{directId}', function (Request $request, $directId) {
        $validator = Validator::make(['directId' => $directId], [
            'directId' => 'required|string|min:2|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $master = \App\Models\Master::where('direct', 'like', "%{$directId}%")
            ->orWhere('instagram', 'like', "%{$directId}%")
            ->first();

        if ($master) {
            return response()->json([
                'status' => 'ok',
                'data' => [
                    'name' => implode(' ', [$master->person->last_name, $master->person->first_name, $master->person->patronymic]),
                    'link' => route('admin.masters.show', $master->id),
                    'phone' => $master->user->phone,
                    'direct_id' => $directId
                ]
            ]);
        }

        return response()->json([
            'status' => 'not_found',
            'data' => [
                'direct_id' => $directId
            ]
        ], 404);
    });

    // Places routes
    Route::get('/places/{place}/availability', function (Request $request, Place $place) {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date_format:Y-m-d'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $response = $place->availableTimesOnDate($request->get('date'));
        return response()->json(['status' => 'ok', 'data' => $response]);
    });

    Route::get('/places/availability', function (Request $request) {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $places = Place::all();
        $response = $places->map(function ($place) use ($request) {
            return [
                'place_id' => $place->id,
                'name' => $place->name,
                'available_times' => $place->availableTimesOnDateRange(
                    $request->input('start_date'),
                    $request->input('end_date')
                )
            ];
        });

        return response()->json([
            'status' => 'ok',
            'data' => $response
        ]);
    });

    Route::get('/places', function (Request $request) {
        $places = Place::select(['id', 'name'])->get();
        return response()->json([
            'status' => 'ok',
            'data' => $places
        ]);
    });

    // Appointment routes
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::get('/places/{place}/free-slots', [AppointmentController::class, 'freeSlots']);
    Route::get('/places/free-slots', [AppointmentController::class, 'freeSlots']);
    Route::get('/places-list', [AppointmentController::class, 'placesList']);
    Route::get('/places-list-with-slots', [AppointmentController::class, 'placesListWithSlots']);
    Route::get('/masters-list', [AppointmentController::class, 'mastersList']);
});

// Sanctum auth routes (если нужны)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json([
        'status' => 'ok',
        'data' => $request->user()
    ]);
});
