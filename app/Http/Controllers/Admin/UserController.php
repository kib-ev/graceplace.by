<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function update(Request $request, User $user)
    {
        if ($request->has('password')) {
            $user->update([
                'password' => bcrypt($request->get('password'))
            ]);
        }
    }
}
