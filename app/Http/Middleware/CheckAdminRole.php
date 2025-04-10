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
        // Проверяем, авторизован ли пользователь и есть ли у него роль admin
        if (Auth::check() && Auth::user()->hasRole('admin')) {
            return $next($request); // Разрешаем доступ
        }

        // Если нет роли admin, перенаправляем или выводим ошибку
        return redirect('/');
    }
}
