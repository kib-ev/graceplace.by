<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MandatoryNotice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MandatoryNoticeController extends Controller
{
    public function index()
    {
        $notices = MandatoryNotice::query()
            ->latest('id')
            ->paginate(20);

        return view('admin.mandatory-notices.index', compact('notices'));
    }

    public function create()
    {
        $masters = User::query()->role('master')->where('is_active', 1)->orderBy('name')->get(['id','name','email']);
        return view('admin.mandatory-notices.create', compact('masters'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'starts_at' => ['nullable', 'date'],
            'days_to_live' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'is_active' => ['nullable', 'boolean'],
            'audience_mode' => ['required', 'in:all_masters,specific'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $notice = new MandatoryNotice();
        $notice->title = $data['title'];
        $notice->body = $data['body'];
        $notice->starts_at = $data['starts_at'] ? Carbon::parse($data['starts_at']) : null;
        $notice->expires_at = isset($data['days_to_live']) ? Carbon::now()->addDays((int)$data['days_to_live']) : null;
        $notice->is_active = (bool)($data['is_active'] ?? false);
        $notice->created_by = auth()->id();
        $notice->save();

        $userIds = [];

        if ($data['audience_mode'] === 'all_masters') {
            $userIds = User::query()
                ->role('master')
                ->where('is_active', 1)
                ->pluck('id')
                ->all();
        } else {
            // specific
            $userIds = collect($data['user_ids'] ?? [])
                ->map(fn($v) => (int)$v)
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($userIds)) {
                $exists = User::query()->whereIn('id', $userIds)->pluck('id')->all();
                $userIds = $exists;
            }
        }

        if (!empty($userIds)) {
            $now = now();
            $rows = array_map(function ($uid) use ($notice, $now) {
                return [
                    'mandatory_notice_id' => $notice->id,
                    'user_id' => $uid,
                    'confirmed_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $userIds);

            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('mandatory_notice_user')->insert($chunk);
            }
        }

        return redirect()->route('admin.mandatory-notices.show', $notice)->with('success', 'Уведомление создано');
    }

    public function show(MandatoryNotice $mandatoryNotice)
    {
        $notice = $mandatoryNotice->loadCount([
            'users as recipients_total' => fn($q) => $q,
            'users as recipients_confirmed' => fn($q) => $q->whereNotNull('confirmed_at'),
        ]);

        $recipients = $mandatoryNotice->users()
            ->select('users.id', 'users.name', 'users.email', 'mandatory_notice_user.confirmed_at')
            ->latest('mandatory_notice_user.confirmed_at')
            ->paginate(20);

        return view('admin.mandatory-notices.show', compact('notice', 'recipients'));
    }

    public function destroy(MandatoryNotice $mandatoryNotice)
    {
        $mandatoryNotice->delete();
        return redirect()->route('admin.mandatory-notices.index')->with('success', 'Удалено');
    }
}
