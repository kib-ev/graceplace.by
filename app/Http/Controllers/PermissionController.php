<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    // Отображение списка мастеров и их прав на отмену записи
    public function index()
    {
        // Получаем всех мастеров (предполагаем, что у мастеров есть роль 'master')
        $users = User::role('master')->get();
        $permissionName = 'cancel appointment';

        return view('admin.permissions.index', compact('users', 'permissionName'));
    }

    public function update(Request $request, User $user)
    {
            $userId = $user->id;

            // Проверяем "cancel appointment"
            if ($request->has("cancel_$userId")) {
                if (!$user->can('cancel appointment')) {
                    $user->givePermissionTo('cancel appointment');
                }
            } else {
                if ($user->can('cancel appointment')) {
                    $user->revokePermissionTo('cancel appointment');
                }
            }

            // Проверяем "add appointment"
            if ($request->has("add_$userId")) {
                if (!$user->can('add appointment')) {
                    $user->givePermissionTo('add appointment');
                }
            } else {
                if ($user->can('add appointment')) {
                    $user->revokePermissionTo('add appointment');
                }
            }

        return redirect()->back()->with('success', 'Права мастеров обновлены');
    }

    // Обновление прав мастеров
    public function updateAll(Request $request)
    {
        // Получаем всех мастеров
        $users = User::all();

        foreach ($users as $user) {
            $userId = $user->id;

            // Проверяем "cancel appointment"
            if ($request->has("cancel_$userId")) {
                if (!$user->can('cancel appointment')) {
                    $user->givePermissionTo('cancel appointment');
                }
            } else {
                if ($user->can('cancel appointment')) {
                    $user->revokePermissionTo('cancel appointment');
                }
            }

            // Проверяем "add appointment"
            if ($request->has("add_$userId")) {
                if (!$user->can('add appointment')) {
                    $user->givePermissionTo('add appointment');
                }
            } else {
                if ($user->can('add appointment')) {
                    $user->revokePermissionTo('add appointment');
                }
            }
        }

        return redirect()->route('admin.permissions.index')->with('success', 'Права мастеров обновлены');
    }
}
