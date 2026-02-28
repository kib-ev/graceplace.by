<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Проверяем, авторизован ли пользователь и есть ли у него роль admin или manager
        if (Auth::check() && Auth::user()->hasAnyRole(['admin', 'manager'])) {
            return $next($request); // Разрешаем доступ
        }

        // Если нет роли admin или manager, перенаправляем или выводим ошибку
        return redirect('/');
    }
}
