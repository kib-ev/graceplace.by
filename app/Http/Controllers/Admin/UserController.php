<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\PaymentRequirement;
use App\Models\StorageBooking;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();

        return view('admin.users.index', compact('users'));
    }

    public function debtors(Request $request)
    {
        $baseQuery = User::query()
            ->select('users.*')
            ->addSelect([
                'appointments_debt_amount_byn' => PaymentRequirement::query()
                    ->selectRaw('COALESCE(SUM(pr2.remaining_amount), 0)')
                    ->from('payment_requirements as pr2')
                    ->join('appointments as a2', 'a2.id', '=', 'pr2.payable_id')
                    ->where('pr2.payable_type', Appointment::class)
                    ->where('pr2.status', 'pending')
                    ->where('pr2.remaining_amount', '>', 0)
                    ->whereNull('a2.deleted_at')
                    ->where(function (Builder $subQuery) {
                        $subQuery->whereNotNull('a2.canceled_at')
                            ->orWhereRaw('TIMESTAMPADD(MINUTE, a2.duration, a2.start_at) <= NOW()');
                    })
                    ->whereColumn('a2.user_id', 'users.id'),
                'storage_debt_amount_byn' => PaymentRequirement::query()
                    ->selectRaw('COALESCE(SUM(pr3.remaining_amount), 0)')
                    ->from('payment_requirements as pr3')
                    ->join('storage_bookings as sb3', 'sb3.id', '=', 'pr3.payable_id')
                    ->where('pr3.payable_type', StorageBooking::class)
                    ->whereIn('pr3.status', PaymentRequirement::UNPAID_REQUIREMENT_STATUSES)
                    ->where('pr3.remaining_amount', '>', 0)
                    ->whereNull('sb3.deleted_at')
                    ->whereNull('sb3.finished_at')
                    ->whereColumn('sb3.user_id', 'users.id'),
            ])
            ->havingRaw('(appointments_debt_amount_byn + storage_debt_amount_byn) > 0');

        $activeCount = (clone $baseQuery)
            ->where('users.is_active', 1)
            ->count();
        $inactiveCount = (clone $baseQuery)
            ->where('users.is_active', 0)
            ->count();

        $query = clone $baseQuery;

        if (in_array((string) $request->get('is_active'), ['0', '1'], true)) {
            $query->where('users.is_active', (int) $request->get('is_active'));
        }

        $users = $query
            ->with('master')
            ->orderByRaw('(appointments_debt_amount_byn + storage_debt_amount_byn) DESC')
            ->orderBy('users.name')
            ->get();

        $totalAppointmentsDebt = (float) $users->sum('appointments_debt_amount_byn');
        $totalStorageDebt = (float) $users->sum('storage_debt_amount_byn');
        $totalDebtAll = $totalAppointmentsDebt + $totalStorageDebt;

        return view('admin.users.debtors', compact(
            'users',
            'activeCount',
            'inactiveCount',
            'totalAppointmentsDebt',
            'totalStorageDebt',
            'totalDebtAll'
        ));
    }

    public function update(Request $request, User $user)
    {
        if ($request->has('password') && $request->filled('password')) {
            $user->update([
                'password' => bcrypt($request->get('password'))
            ]);
        }

        if ($request->has('admin') && $user->id !== auth()->id() && $user->id !== 1) {
            if ($request->boolean('admin')) {
                $user->assignRole('admin');
            } else {
                $user->removeRole('admin');
            }
        }

        if ($request->has('manager') && $user->id !== auth()->id() && $user->id !== 1) {
            if ($request->boolean('manager')) {
                $user->assignRole('manager');
            } else {
                $user->removeRole('manager');
            }
        }

        return redirect()->route('admin.users.index')->with('success', 'Обновлено');
    }
}
