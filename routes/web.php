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

if (request()->has('dev') && file_exists(base_path('/routes/_dev.php'))) {
    include_once (base_path('/routes/_dev.php'));
}

Route::get('/masters/{}', function () {
    $date = request('date');
    return view('welcome', compact('date'));
});

Route::get('/gpt', function () {
    return view('gpt');
});

Route::get('/schedule', function () {
    $date = request('date');

    if(is_null($date)) {
        return redirect()->to('https://graceplace.by/schedule?date=' . now()->format('Y-m-d'));
    }

    return view('public/index2', compact('date'));
});

Route::get('/', function () {
    $date = request('date');

    if(is_null($date)) {
        return redirect()->to('https://graceplace.by?date=' . now()->format('Y-m-d'));
    }

    return view('public/index2', compact('date'));
});

Route::get('/index1', function () {
    $date = request('date');

//    if(is_null($date)) {
//        return redirect()->to('https://graceplace.by?date=' . now()->format('Y-m-d'));
//    }

    return view('public/index', compact('date'));
});

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {

    Route::get('/', function () {
        return redirect()->to('/admin/appointments');
    });

    Route::resource('masters', \App\Http\Controllers\Admin\MasterController::class);
    Route::resource('appointments', \App\Http\Controllers\AppointmentController::class);
    Route::resource('places', \App\Http\Controllers\PlaceController::class);
    Route::resource('compartments', \App\Http\Controllers\CompartmentController::class);

    Route::get('stats', function () {
        return view('admin.stats');
    });

    Route::get('logs', function () {
        return view('admin.logs');
    });

    // COMMENTS
    Route::resource('comments', \App\Http\Controllers\CommentController::class)->only(['store', 'destroy']);

    // RENTS
    Route::resource('rents', \App\Http\Controllers\RentController::class);

    // USER
    Route::get('/user/{user}/schedule', function ($user) {
        $master = \App\Services\AppointmentService::getMasterByUserId($user);
        return view('user.schedule', compact('master'));
    });
});

Route::name('public.')->middleware(['auth'])->group(function () {
    Route::resource('masters', \App\Http\Controllers\Public\MasterController::class)->only(['show', 'index']);
    Route::resource('appointments', \App\Http\Controllers\Public\AppointmentController::class)->only(['store']);
});

Auth::routes(['register' => false]);

Route::get('/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    return redirect('/');
});

Route::get('/home', function () {
    return redirect()->to('/');
})->name('home');


Route::get('/test', function () {


    $value = \App\Services\AppointmentService::getMasterByUserId(1);

    dump($value);


    return view('welcome');
});
