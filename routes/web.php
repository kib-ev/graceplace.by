<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::name('public.')->group(function () {
    Route::resource('masters', \App\Http\Controllers\MasterController::class);
    Route::resource('appointments', \App\Http\Controllers\AppointmentController::class);
    Route::resource('places', \App\Http\Controllers\PlaceController::class);
});
