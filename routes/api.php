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
    Route::get('/v2/user/{username}', function (Request $request, $username) {
        $validator = Validator::make(['username' => $username], [
            'username' => 'required|string|min:2|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $master = \App\Models\Master::where('instagram', 'like', "%{$username}%")->first();

        if ($master) {
            return response()->json([
                'status' => 'ok',
                'name' => implode(' ', [$master->person->last_name, $master->person->first_name, $master->person->patronymic]),
                'link' => route('admin.masters.show', $master->id),
                'phone' => $master->user->phone,
                'username' => $username
            ]);
        }

        return response()->json([
            'status' => 'not_found',
            'username' => $username
        ], 404);
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/places/{place}/availability', function (Request $request, \App\Models\Place $place) {
    if($request->has('date')) {
        $date = $request->get('date');
        $response = $place->availableTimesOnDate($date);

        return \Illuminate\Support\Facades\Response::json(['data' => $response]);
    }
});


Route::get('/places/availability', function (Request $request) {

    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');

    $places = Place::all();
    $response = [];

    foreach ($places as $place) {
        $availableTimes = $place->availableTimesOnDateRange($startDate, $endDate);
        $response[] = [
            'place_id' => $place->id,
            'name' => $place->name,
//                'location' => $place->location,
            'available_times' => $availableTimes
        ];
    }

    return response()->json([
        'data' => $response
    ]);

});

Route::get('/places', function (Request $request) {

    $places = Place::all();

    $response = [];
    foreach ($places as $place) {
        $response[] = [
            'place_id' => $place->id,
            'name' =>  $place->name,
        ];
    }

    return response()->json([
        'data' => $response
    ]);

});

Route::middleware(['api_token'])->group(function() {
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::get('/places/{place}/free-slots', [AppointmentController::class, 'freeSlots']);
    Route::get('/places/free-slots', [AppointmentController::class, 'freeSlots']);
    Route::get('/places-list', [AppointmentController::class, 'placesList']);
    Route::get('/places-list-with-slots', [AppointmentController::class, 'placesListWithSlots']);
    Route::get('/masters-list', [AppointmentController::class, 'mastersList']);
});
