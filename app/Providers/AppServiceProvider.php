<?php

namespace App\Providers;

use App\Http\View\Composers\MastersWithDebtComposer;
use App\Models\Appointment;
use App\Models\Master;
use App\Observers\AppointmentObserver;
use App\Observers\MasterObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Appointment::observe(AppointmentObserver::class);
        Master::observe(MasterObserver::class);
        View::composer('admin.layouts.includes.menu', MastersWithDebtComposer::class);
    }
}
