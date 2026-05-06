<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class PluginController extends Controller
{
    public function index()
    {
        $apiToken = session('new_api_token', config('services.api.token'));

        return view('admin.plugin.index', [
            'apiToken' => $apiToken,
            'generatedNow' => session('generated_now', false),
        ]);
    }

    public function regenerateApiToken()
    {
        Artisan::call('api:generate-token');

        $output = Artisan::output();
        preg_match('/Новый токен:\s*(\S+)/u', $output, $matches);
        $newToken = $matches[1] ?? null;

        return redirect()
            ->route('admin.plugin.index')
            ->with('generated_now', true)
            ->with('new_api_token', $newToken)
            ->with('token_output', trim($output));
    }
}
