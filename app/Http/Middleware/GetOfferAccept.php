<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GetOfferAccept
{
    public array $blackList = [];

    protected array $except = [
        '/public-offer',
        '/public-offer/accept',
        '/logout',
        '/appointments/*/cancel',
    ];

    public function handle(Request $request, Closure $next)
    {
        $currentUser = Auth::user();

        Log::info($request->getRequestUri());

        if (!in_array($request->getRequestUri(), $this->except) && $currentUser && in_array($currentUser->id, $this->blackList) && is_null($currentUser->offer_accept_date)) {
            return redirect()->to('/public-offer');
        } else {
            return $next($request);
        }
    }

    public function inExcepted($uri) {
        $uriParts = explode('/', $uri);

        foreach ($this->except as $exceptUri) {
            $exceptUriParts = explode('/', $exceptUri);
            $result = [];

            for ($i = 0; $i < max(count($uriParts), count($exceptUriParts)); $i++) {
                if (isset($uriParts[$i]) && isset($exceptUriParts[$i])) {
                    if((($uriParts[$i] == $exceptUriParts[$i]) || ($exceptUriParts[$i] == '*'))) {
                        $result[$i] = true;
                    } else {
                        $result[$i] = false;
                    }
                } else {
                    $result[$i] = false;
                }
            }
            if (!in_array(false, $result, true)) {
                return true;
            }
        }

        return false;
    }
}
