<?php

use Illuminate\Http\Request;
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

    return view('public/index', compact('date'));
});

Route::get('/', function () {
    $date = request('date');

    if(is_null($date)) {
        return redirect()->to('https://graceplace.by?date=' . now()->format('Y-m-d'));
    }

    return view('public/index', compact('date'));
});

Route::get('/index1', function () {
    $date = request('date');

//    if(is_null($date)) {
//        return redirect()->to('https://graceplace.by?date=' . now()->format('Y-m-d'));
//    }

    return view('public/index', compact('date'));
});


Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {

    // APPOINTMENTS
    Route::get('/', function () {
        return redirect()->to('/admin/appointments');
    });

    Route::get('/appointments/merge-closest', [\App\Http\Controllers\Admin\AppointmentController::class, 'mergeClosestAppointments'])
        ->name('appointments.merge-closest');

    Route::resource('appointments', \App\Http\Controllers\Admin\AppointmentController::class);
    Route::post('/appointments/{appointment}/pay', function (Request $request, \App\Models\Appointment $appointment) {
        $amount = $request->get('amount');
        $useBalance = $request->get('use_balance') == 'on';

        if(is_null($appointment->price)) {
            $user = $appointment->user;

            if($amount > 0) {
                if (!$useBalance) {
                    $user->deposit($amount, 'Appointment ID: ' . $appointment->id . ' <<< ADD CASH', $request->get('created_at'));
                }
                $user->withdraw($amount, 'Appointment ID: ' . $appointment->id . ' <<< PLACE RENT', $request->get('created_at'));
            }

            $appointment->update([
                'price' => $amount
            ]);
        }

        return back();

    })->name('appointments.pay');


    // APPOINTMENTS PAYMENTS
    Route::get('/payments', [\App\Http\Controllers\PaymentController::class, 'index'])->name('payments.index');
    Route::patch('/payments/{payment}/update-status', [\App\Http\Controllers\PaymentController::class, 'updateStatus'])->name('payments.update-status');
    Route::get('/payments/{payment}/delete', [\App\Http\Controllers\PaymentController::class, 'destroy'])->name('payments.destroy'); // todo refactor
    Route::post('/appointments/payments', [\App\Http\Controllers\PaymentController::class, 'store'])->name('appointments.payments.store'); // todo refactor
    // APPOINTMENTS REQUIREMENTS
    Route::get('/appointments/{appointment}/payments/', [\App\Http\Controllers\Admin\AppointmentController::class, 'payments'])->name('appointments.payments.show');
    Route::post('/appointments/payment-requirements', [\App\Http\Controllers\PaymentRequirementController::class, 'store'])->name('appointments.payment-requirements.store');
    Route::get('/payment-requirements/{id}/destroy', [\App\Http\Controllers\PaymentRequirementController::class, 'destroy'])->name('payment-requirements.destroy'); // todo refactor


    // OTHER
    Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminController::class, 'index'])->name('dashboard');

    Route::get('stats', function () {
        return view('admin.stats');
    });

    // MASTERS
    Route::resource('masters', \App\Http\Controllers\Admin\MasterController::class);

    // PLACES
    Route::resource('places', \App\Http\Controllers\PlaceController::class);

    // LOGS
    Route::get('logs', function () {
        return view('admin.logs');
    });

    // COMMENTS
    Route::resource('comments', \App\Http\Controllers\CommentController::class)->only(['store', 'destroy']);

    // STORAGE CELLS BOOKING
    Route::resource('storage-cells', \App\Http\Controllers\StorageCellController::class);
    Route::resource('storage-bookings', \App\Http\Controllers\Admin\StorageBookingController::class);

    // USERS
    Route::get('/user/{user}/schedule', function ($user) {
        $master = $user->master;
        return view('user.schedule', compact('master'));
    });
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);

    Route::get('/users', function () {
        return view('admin.users.index');
    })->name('users.index');
    Route::get('users/{user}/login', function ($user) {
        \Illuminate\Support\Facades\Auth::login($user);
        return redirect()->to('/');
    });

    // TRANSACTIONS
    Route::resource('transactions', \App\Http\Controllers\UserTransactionController::class)->only(['index', 'destroy', 'store']);

    // PERMISSIONS
    Route::get('/permissions', [\App\Http\Controllers\PermissionController::class, 'index'])->name('permissions.index');
    Route::post('/permissions/cancel-appointment', [\App\Http\Controllers\PermissionController::class, 'update'])->name('permissions.update');

    // E-POS
    Route::get('/orders-epos', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders-epos/create', [\App\Http\Controllers\Admin\OrderController::class, 'create'])->name('orders.create');
    Route::post('/orders-epos', [\App\Http\Controllers\Admin\OrderController::class, 'completeApiRequest'])->name('orders.store');

    // BALANCES
    Route::get('balances', [\App\Http\Controllers\BalanceController::class, 'index']);


    // USER SETTINGS ADMIN
    Route::post('/settings', function () {

        $user = \App\Models\User::find(request('user_id'));
        $user->setSetting(request('key'), request('value'));

        return redirect()->back()->with('success', 'Настройки успешно сохранены');
    })->name('update-settings');
});

Route::post('/test', function () {
    return 123;
});

Route::name('public.')->middleware(['auth'])->group(function () {
    // MASTERS
    Route::resource('masters', \App\Http\Controllers\Public\MasterController::class)->only(['show', 'index']);

    // APPOINTMENTS
    Route::resource('appointments', \App\Http\Controllers\Public\AppointmentController::class)->only(['store']);

    Route::any('/appointments/{appointment}/cancel', [\App\Http\Controllers\Public\AppointmentController::class, 'cancelAppointment'])->name('appointments.cancel');

    Route::resource('places', \App\Http\Controllers\Public\PlaceController::class)->only('show');
});


Route::name('user.')->prefix('/user')->middleware(['auth'])->group(function () {

    // USER SETTINGS
    Route::post('/settings', function () {
        $user = auth()->user();

        // Сохранение настройки видимости рабочих мест
        $workspaceVisibility = request()->input('workspace_visibility', []);
        $user->setSetting('workspace_visibility', $workspaceVisibility);

        return redirect()->back()->with('success', 'Настройки успешно сохранены');
    })->name('update-settings');

    // APPOINTMENTS
    Route::resource('appointments', \App\Http\Controllers\User\AppointmentController::class)->only(['store']);

    // CANCEL APPOINTMENT
    Route::post('/appointments/{appointment}/cancel', [\App\Http\Controllers\User\AppointmentController::class, 'cancelAppointment'])
        ->middleware('check.cancellation')
        ->name('appointments.cancel');

    // TRANSACTIONS
    Route::get('/transactions', function () {
        $transactions = \App\Models\UserTransaction::where('user_id', \Illuminate\Support\Facades\Auth::id())->get();
        return view('user.transactions.index', compact('transactions'));
    });

    Route::get('/documents/{appointmentId}', function (Request $request, $appointmentId) {

        $appointment = \App\Models\Appointment::find($appointmentId);

        if($appointment && (auth()->user()->hasRole(['admin']) || auth()->id() == $appointment->user_id)) {

            if($request->has('html')) {
                return view('user.documents.show', compact('appointment'));
            } else {
                $mpdf = new \Mpdf\Mpdf();
                $html = view('user.documents.show', compact('appointment'))->render();
                $filename = 'doc_' . $appointmentId;

                $mpdf->WriteHTML($html);
                $mpdf->Output($filename, 'I');
            }
        }

        return abort(404);
    });
});

Auth::routes();

Route::get('/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    return redirect('/');
});

Route::get('/home', function () {
    return redirect()->to('/');
})->name('home');


Route::get('/pay', function () {
    return redirect()->to('https://pay.raschet.by/#00020132520010by.raschet01074440631101722420-2-i20250128120211530393354040.006304BAE7');
});

Route::get('/public-offer', function () {
    return view('public.offers.offer-20250101');
});

Route::post('/public-offer/accept', function () {
    $user = auth()->user();

    if ($user) {
        $user->update([
            'offer_accept_date' => now()
        ]);

        return redirect()->route('home');
    }
});


Route::get('/test', function () {
    return view('test');
});
