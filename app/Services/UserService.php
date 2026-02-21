<?php

namespace App\Services;

use App\Models\Master;
use App\Models\Person;
use App\Models\Phone;
use App\Models\Place;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class UserService
{
    public function createUserMaster($phone, $firstName, $lastName, $patronymic, $description = null, $instagram = null, $direct = null): Master
    {
        return DB::transaction(function () use ($phone, $firstName, $lastName, $patronymic, $description, $instagram, $direct) {
            $person = Person::make();
            $person->fill([
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'patronymic' => $patronymic,
            ])->save();

            /* @var $user User */
            $user = User::updateOrCreate([
                'email' => Str::replace(['+'], '', $phone . '@graceplace.by'),
            ], [
                'phone' => $phone,
                'name'  => implode(' ', [$lastName, $firstName]),
                'password' => '-',
            ]);

            $master = Master::create([
                'user_id'     => $user->id,
                'description' => $description,
                'person_id'   => $person->id,
                'instagram'   => isset($instagram) ? explode('?', $instagram)[0] : '',
                'direct'      => $direct,
            ]);

            $user->update([
                'password' => bcrypt('graceplace' . $master->id),
            ]);

            $user->assignRole('master');
            $user->givePermissionTo('cancel appointment');
            $user->givePermissionTo('add appointment');

            Phone::firstOrCreate(
                ['number' => $phone],
                ['person_id' => $person->id]
            );

            $placesId = Place::get()->pluck('id');
            $user->setSetting('workspace_visibility', $placesId);

            return $master;
        });
    }
}
