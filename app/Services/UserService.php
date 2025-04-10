<?php

namespace App\Services;

use App\Models\Master;
use App\Models\Person;
use App\Models\Phone;
use App\Models\Place;
use App\Models\User;
use Illuminate\Support\Str;

final class UserService
{
    public function createUserMaster($phone, $firstName, $lastName, $patronymic, $description = null, $instagram = null, $direct = null): Master
    {
        $person = Person::make();
        $person->fill([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'patronymic' => $patronymic
        ])->save();

        /* @var $user User */
        $user = User::updateOrCreate([
            'phone' => $phone,
            'email' => Str::replace(['+'], '', $phone. '@graceplace.by'),
        ], [
            'name' => implode(' ', [$lastName, $firstName]),
            'password' => '-'
        ]);

        $master = Master::create([
            'user_id' => $user->id,
            'description' => $description,
            'person_id' => $person->id,
            'instagram' => isset($instagram) ? explode('?', $instagram)[0] : '',
            'direct' => $direct,
        ]);

        // SET PASSWORD
        $user->update([
            'password' => bcrypt('graceplace' . $master->id)
        ]);

        // PERMISSION
        $user->assignRole('master');
        $user->givePermissionTo('cancel appointment');

        $phone = Phone::create([
            'number' => $phone,
            'person_id' => $person->id,
        ]);

        // DEFAULT SETTINGS
        $placesId = Place::get()->pluck('id');
        $user->setSetting('workspace_visibility', $placesId);

        return $master;
    }
}
