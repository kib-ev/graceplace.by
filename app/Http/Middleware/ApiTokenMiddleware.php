<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ApiTokenMiddleware
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
        $token = $request->header('X-API-Token');
        
        if (!$token) {
            return response()->json([
                'error' => 'API Token не предоставлен',
                'status' => 'error'
            ], 401);
        }

        // Получаем токен из конфигурации
        $validToken = config('services.api.token');

        if (!$validToken) {
            return response()->json([
                'error' => 'API Token не настроен на сервере',
                'status' => 'error'
            ], 500);
        }

        if ($token !== $validToken) {
            return response()->json([
                'error' => 'Неверный API Token',
                'status' => 'error'
            ], 401);
        }

        return $next($request);
    }
} 