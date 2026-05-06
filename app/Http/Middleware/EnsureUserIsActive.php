<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Для админов и менеджеров ограничение не применяем.
        if ($user->hasAnyRole(['admin', 'manager'])) {
            return $next($request);
        }

        if (!(bool) $user->is_active) {
            return redirect()->route('user.pending-approval');
        }

        return $next($request);
    }
}

