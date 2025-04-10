<?php

use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


Route::get('/user/{directId}', function (Request $request, $directId) {

    $master = \App\Models\Master::where('direct', 'like', "%{$directId}%")->orWhere('instagram', 'like', "%{$directId}%")->first();

    if ($master) {
        return [
            'name' => implode(' ', [$master->person->last_name, $master->person->first_name, $master->person->patronymic]),
            'link' => route('admin.masters.show', $master->id),
            'status' => 'ok',
            'phone' => $master->user->phone,
            'direct_id' => $directId
        ];
    }

    return [
        'status' => null,
        'direct_id' => $directId
    ];

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
