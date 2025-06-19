<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-API-Token');
        
        if (!$token) {
            return response()->json([
                'error' => 'API token is missing',
                'status' => 'error'
            ], 401);
        }

        // Проверяем токен из конфига или базы данных
        $validToken = Config::get('services.api.token');
        if (!$validToken || $token !== $validToken) {
            return response()->json([
                'error' => 'Invalid API token',
                'status' => 'error'
            ], 403);
        }

        return $next($request);
    }
} 