<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckMasterRole
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->hasRole('master')) {
            return $next($request);
        }

        return redirect('/')->with('error', 'Доступ запрещен. Необходимы права мастера.');
    }
} 