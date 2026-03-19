<?php

namespace App\Services;

use App\Models\Master;
use App\Models\Place;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UserService
{
    public function createUserMaster($phone, $firstName, $lastName, $patronymic, $description = null, $instagram = null, $direct = null): Master
    {
        $email = user_email_from_phone_number($phone);

        if (User::where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'phone' => ['Пользователь с таким номером телефона уже зарегистрирован.'],
            ]);
        }

        return DB::transaction(function () use ($phone, $firstName, $lastName, $patronymic, $description, $instagram, $direct, $email) {
            $user = User::create([
                'email'    => $email,
                'phone'    => $phone,
                'name'     => implode(' ', array_filter([$lastName, $firstName])),
                'password' => '-',
            ]);

            $master = Master::create([
                'user_id'     => $user->id,
                'first_name'  => $firstName,
                'last_name'   => $lastName,
                'patronymic'  => $patronymic,
                'description' => $description,
                'instagram'   => isset($instagram) ? explode('?', $instagram)[0] : '',
                'direct'      => $direct,
            ]);

            $user->update([
                'password' => bcrypt('graceplace' . $master->id),
            ]);

            $user->assignRole('master');
            $user->givePermissionTo('cancel appointment');
            $user->givePermissionTo('add appointment');

            $placesId = Place::get()->pluck('id');
            $user->setSetting('workspace_visibility', $placesId);

            return $master;
        });
    }
}
