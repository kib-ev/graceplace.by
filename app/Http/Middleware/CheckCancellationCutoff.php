<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCancellationCutoff
{
    public function handle($request, Closure $next)
    {
        $appointment = $request->route('appointment');

        if (!$appointment->canBeCancelledByUser()) {
            return redirect()->back()->with('error', 'Отмена записи недоступна менее чем за ' . CANCELLATION_CUTOFF_HOURS . ' ч.');
        }

        return $next($request);
    }
}
