<?php

namespace App\Http\Middleware;

use App\Models\MandatoryNotice;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequireNoticeConfirmation
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        // Пропускаем собственные маршруты подтверждения, чтобы избежать циклических редиректов
        if ($request->routeIs('user.notices.*')) {
            return $next($request);
        }

        $userId = Auth::id();

        $pending = MandatoryNotice::query()
            ->active()
            ->inLifetime()
            ->whereHas('users', function ($q) use ($userId) {
                $q->where('user_id', $userId)->whereNull('confirmed_at');
            })
            ->orderByRaw('COALESCE(starts_at, created_at) asc')
            ->first();

        if ($pending) {
            // Сохраним intended-URL и отправим на подтверждение
            $request->session()->put('url.intended', $request->fullUrl());
            return redirect()->route('user.notices.show');
        }

        return $next($request);
    }
}
