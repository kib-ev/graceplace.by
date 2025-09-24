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

Route::get('/masters/{master}', [\App\Http\Controllers\Public\MasterController::class, 'show'])->name('public.masters.show');

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

// PUBLIC
Route::get('/', function (Request $request) {
    if (auth()->check()) {
        $dateParam = $request->get('date');
        $dateStr = $dateParam ? \Illuminate\Support\Carbon::parse($dateParam)->toDateString() : now()->format('Y-m-d');
        return redirect()->route('user.schedule', ['date' => $dateStr]);
    }

    $request->validate([
        'date'  => 'date',
    ]);

    $date = \Illuminate\Support\Carbon::parse($request->get('date'));

    if(is_null($date)) {
        return redirect()->to('https://graceplace.by?date=' . now()->format('Y-m-d'));
    }

    return view('public/index', compact('date'));
});

// USER
Route::prefix('user')->name('user.')->middleware(['auth', 'notice.required'])->group(function () { // notice.required

    Route::get('/schedule', function (Request $request) {
        $request->validate([
            'date'  => 'date',
        ]);

        $dateInput = $request->get('date');

        if (!$dateInput) {
            return redirect()->route('user.schedule', ['date' => now()->format('Y-m-d')]);
        }

        $date = \Illuminate\Support\Carbon::parse($dateInput);

        return view('public/index', compact('date'));
    })->name('schedule');
});

// Роуты уведомлений (доступны мастерам без блока notice.required)
Route::name('user.')->prefix('/user')->middleware(['auth', 'master'])->group(function () {
    Route::get('/notices/pending', [\App\Http\Controllers\User\MandatoryNoticeController::class, 'show'])->name('notices.show');
    Route::post('/notices/confirm', [\App\Http\Controllers\User\MandatoryNoticeController::class, 'confirm'])->name('notices.confirm');
    Route::get('/notices/history', [\App\Http\Controllers\User\MandatoryNoticeController::class, 'history'])->name('notices.history');
});

// Маршруты расписания (требуют роль мастера)
//Route::name('user.')->prefix('/user')->middleware(['auth', 'master'])->group(function () {
//    Route::get('/schedule', [App\Http\Controllers\User\ScheduleController::class, 'index'])->name('schedule.index');
//    Route::post('/schedule', [App\Http\Controllers\User\ScheduleController::class, 'store'])->name('schedule.store');
//    Route::post('/schedule/delete', [App\Http\Controllers\User\ScheduleController::class, 'delete'])->name('schedule.delete');
//    Route::get('/schedule/all-intervals', [App\Http\Controllers\User\ScheduleController::class, 'getAllIntervals'])->name('schedule.all-intervals');
//    Route::get('/schedule/csrf', [App\Http\Controllers\User\ScheduleController::class, 'refreshCsrf'])->name('schedule.csrf');
//});

Route::get('/booking', function (Request $request) {
    $request->validate([
        'date'  => 'date',
    ]);

    $date = \Illuminate\Support\Carbon::parse($request->get('date'));

    if(is_null($date)) {
        // todo change
        return redirect()->to('/booking?date=' . now()->format('Y-m-d'));
    }

    return view('public/booking/index', compact('date'));
});

Route::post('/booking', [\App\Http\Controllers\Public\BookingController::class, 'store'])->name('public.booking.store');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {

    // APPOINTMENTS
    Route::get('/', function () {
        return redirect()->to('/admin/appointments');
    });

    Route::get('appointments-stats', function () {
        $stats = DB::table('appointments as a')
            ->select(
                'a.place_id',
                DB::raw('HOUR(DATE_ADD(a.start_at, INTERVAL n.n HOUR)) as hour_of_day'),
                DB::raw('COUNT(*) as total_appointments')
            )
            ->join(DB::raw('
        (SELECT 0 AS n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3
         UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6
         UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10) n
    '), DB::raw('n.n'), '<=', DB::raw('FLOOR(a.duration / 60)'))
            ->whereNull('a.canceled_at')
            ->groupBy('a.place_id', DB::raw('hour_of_day'))
            ->orderBy('a.place_id')
            ->orderBy('hour_of_day')
            ->get();
        return view('admin.appointments_stats', compact('stats'));
    });

    Route::get('appointments-chart', function (Request $request) {
        $startDate = $request->input('start_date', now()->startOfYear()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $stats = DB::table('appointments as a')
            ->select(
                'a.place_id',
                DB::raw('HOUR(DATE_ADD(a.start_at, INTERVAL n.n HOUR)) as hour_of_day'),
                DB::raw('COUNT(*) as total_appointments')
            )
            ->join(DB::raw('
            (SELECT 0 AS n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3
             UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6
             UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10) n
        '), DB::raw('n.n'), '<=', DB::raw('FLOOR(a.duration / 60)'))
            ->whereNull('a.canceled_at')
            ->whereBetween('a.start_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('a.place_id', DB::raw('hour_of_day'))
            ->orderBy('a.place_id')
            ->orderBy('hour_of_day')
            ->get();

        // Преобразуем данные для графика
        $chartData = [];
        foreach ($stats as $stat) {
            $chartData[$stat->place_id][$stat->hour_of_day] = $stat->total_appointments;
        }

        return view('admin.appointments_chart', compact('chartData'));
    });


    Route::get('/appointments/cancel-stats', function (Request $request) {
        $startDate = $request->input('start_date', now()->startOfYear()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $losses = DB::table('appointments as a')
            ->join('places as p', 'a.place_id', '=', 'p.id')
            ->select(
                'a.place_id',
                'p.name as place_name',
                DB::raw('COUNT(*) as cancellations_count'),
                DB::raw('SUM(CEIL(a.duration / 60) * p.price_per_hour * 0.5) as potential_loss')
            )
            ->whereNotNull('a.canceled_at')
            ->whereRaw('TIMESTAMPDIFF(HOUR, a.canceled_at, a.start_at) < 24')
            ->whereBetween('a.canceled_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('a.place_id', 'p.name')
            ->get();

        return view('admin.appointments_cancel_stats', compact('losses', 'startDate', 'endDate'));
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

    // MANDATORY NOTICES
    Route::resource('mandatory-notices', \App\Http\Controllers\Admin\MandatoryNoticeController::class);

    // OTHER
    Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminController::class, 'index'])->name('dashboard');

    Route::get('stats', [\App\Http\Controllers\Admin\StatsController::class, 'index']);

    Route::get('/download/chrome-extension', [\App\Http\Controllers\Admin\AdminController::class, 'downloadChromeExtension'])->name('download.chrome-extension');

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
    Route::post('/permissions/update-all', [\App\Http\Controllers\PermissionController::class, 'updateAll'])->name('permissions.update-all');
    Route::post('/permissions/{user}/update', [\App\Http\Controllers\PermissionController::class, 'update'])->name('permissions.update');

    // E-POS
    Route::get('/orders-epos', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders-epos/create', [\App\Http\Controllers\Admin\OrderController::class, 'create'])->name('orders.create');
    Route::post('/orders-epos', [\App\Http\Controllers\Admin\OrderController::class, 'completeApiRequest'])->name('orders.store');

    // BALANCES
    Route::get('balances', [\App\Http\Controllers\BalanceController::class, 'index']);


    // USER SETTINGS ADMIN
    Route::post('/settings', function () {
        $user = \App\Models\User::find(request('user_id'));

        if (!$user) {
            return redirect()->back()->withErrors('Пользователь не найден');
        }

        // Сохраняем оба значения
        $user->setSetting('payment_link.place', request('payment_link_place'));
        $user->setSetting('payment_link.storage', request('payment_link_storage'));

        return redirect()->back()->with('success', 'Настройки успешно сохранены');
    })->name('update-settings');

    Route::get('/api', function() {
        return view('admin.api');
    })->name('api');
});

Route::post('/test', function () {
    return 123;
});

Route::name('public.')->middleware(['auth'])->group(function () {
    // MASTERS
    Route::resource('masters', \App\Http\Controllers\Public\MasterController::class)->only(['show', 'index']);

    // APPOINTMENTS
//    Route::resource('appointments', \App\Http\Controllers\Public\AppointmentController::class)->only(['store']);
//    Route::any('/appointments/{appointment}/cancel', [\App\Http\Controllers\Public\AppointmentController::class, 'cancelAppointment'])->name('appointments.cancel');

    Route::resource('places', \App\Http\Controllers\Public\PlaceController::class)->only('show');

});

// Страница мастера для клиентов
Route::get('/book/{master}', [\App\Http\Controllers\Public\BookingController::class, 'show'])->name('booking.show');

// Обработка бронирования
Route::post('/book/{master}/reserve', [\App\Http\Controllers\Public\BookingController::class, 'reserve'])->name('booking.reserve');


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
})->name('user.logout');

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

use App\Http\Controllers\TicketController;



// Роуты для пользователей (мастеров)
Route::prefix('user')
    ->middleware(['auth']) // или другая роль по твоей системе
    ->name('user.tickets.')
    ->group(function () {
        Route::get('tickets', [TicketController::class, 'index'])->name('index');
        Route::get('tickets/create', [TicketController::class, 'create'])->name('create');
        Route::post('tickets', [TicketController::class, 'store'])->name('store');
        Route::get('tickets/{ticket}', [TicketController::class, 'show'])->name('show');
        Route::get('tickets/{ticket}/edit', [TicketController::class, 'edit'])->name('edit');
        Route::put('tickets/{ticket}', [TicketController::class, 'update'])->name('update');
        Route::delete('tickets/{ticket}', [TicketController::class, 'destroy'])->name('destroy');
    });

// Роуты для админки
Route::prefix('admin')
    ->middleware(['auth'])
    ->name('admin.tickets.')
    ->group(function () {
        Route::get('tickets', [TicketController::class, 'index'])->name('index');
        Route::get('tickets/create', [TicketController::class, 'create'])->name('create');
        Route::post('tickets', [TicketController::class, 'store'])->name('store');
        Route::get('tickets/{ticket}', [TicketController::class, 'show'])->name('show');
        Route::get('tickets/{ticket}/edit', [TicketController::class, 'edit'])->name('edit');
        Route::put('tickets/{ticket}', [TicketController::class, 'update'])->name('update');
        Route::delete('tickets/{ticket}', [TicketController::class, 'destroy'])->name('destroy');
    });

Route::post('/appointments', [\App\Http\Controllers\Public\AppointmentController::class, 'store'])->name('public.appointments.store');

Route::middleware(['auth'])->group(function () {
    Route::post('/appointments', [\App\Http\Controllers\User\AppointmentController::class, 'store'])->name('user.appointments.store');
    Route::post('/appointments/{appointment}/cancel', [\App\Http\Controllers\User\AppointmentController::class, 'cancelAppointment'])->name('user.appointments.cancel');
});

