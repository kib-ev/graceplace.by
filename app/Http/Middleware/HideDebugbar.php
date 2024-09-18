<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HideDebugbar
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (class_exists(\Debugbar::class) && auth()->id() != 1) {
            \Debugbar::disable();
        }
        return $next($request);
    }
}
