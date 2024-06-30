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

Route::get('/1', function () {
    $date = request('date');
    return view('welcome', compact('date'));
});

Route::get('/', function () {
    $date = request('date');

    if(is_null($date)) {
        return redirect()->to('https://graceplace.by?date=' . now()->format('Y-m-d'));
    }


    return view('index', compact('date'));
});

Route::get('/admin/stats', function () {
    return view('admin.stats');
});

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::resource('masters', \App\Http\Controllers\MasterController::class);
    Route::resource('appointments', \App\Http\Controllers\AppointmentController::class);
    Route::resource('places', \App\Http\Controllers\PlaceController::class);
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
