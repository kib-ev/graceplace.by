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

    // Обновление прав мастеров
    public function update(Request $request)
    {
        $permissionName = 'cancel appointment';

        // Получаем всех мастеров
        $users = User::get();

        // Обрабатываем каждого мастера
        foreach ($users as $user) {
            // Если мастер выбран, проверяем, есть ли у него уже это разрешение
            if ($request->has('master_' . $user->id)) {
                if (!$user->can($permissionName)) {
                    // Назначаем разрешение, если его нет
                    $user->givePermissionTo($permissionName);
                }
            } else {
                // Если мастер не выбран, удаляем разрешение
                if ($user->can($permissionName)) {
                    $user->revokePermissionTo($permissionName);
                }
            }
        }

        return redirect()->route('admin.permissions.index')->with('success', 'Права мастеров обновлены');
    }
}
