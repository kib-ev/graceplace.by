<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAdminOnlyRole
{
    /**
     * Доступ только для роли admin (не manager).
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->hasRole('admin')) {
            return $next($request);
        }

        return redirect('/admin')->with('error', 'Недостаточно прав');
    }
}
