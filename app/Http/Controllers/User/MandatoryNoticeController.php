<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\MandatoryNotice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MandatoryNoticeController extends Controller
{
    public function show()
    {
        $userId = Auth::id();

        $notice = MandatoryNotice::query()
            ->active()
            ->inLifetime()
            ->whereHas('users', function ($q) use ($userId) {
                $q->where('user_id', $userId)->whereNull('confirmed_at');
            })
            ->orderByRaw('COALESCE(starts_at, created_at) asc')
            ->first();

        if (!$notice) {
            return redirect()->intended(route('home'));
        }

        return view('user.mandatory-notices.show', compact('notice'));
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'notice_id' => ['required', 'integer', 'exists:mandatory_notices,id'],
        ]);

        $user = Auth::user();

        $notice = MandatoryNotice::query()
            ->active()
            ->inLifetime()
            ->whereKey($request->integer('notice_id'))
            ->whereHas('users', function ($q) use ($user) {
                $q->where('user_id', $user->id)->whereNull('confirmed_at');
            })
            ->firstOrFail();

        $notice->users()->updateExistingPivot($user->id, [
            'confirmed_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->intended(route('user.notices.show'));
    }

    public function history()
    {
        $user = Auth::user();

        $notices = $user->mandatoryNotices()
            ->wherePivotNotNull('confirmed_at')
            ->orderByPivot('confirmed_at', 'desc')
            ->paginate(20);

            return view('user.mandatory-notices.history', compact('notices'));
    }
}
