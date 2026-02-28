<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
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
