<?php

namespace App\Observers;

use App\Models\Master;

class MasterObserver
{
    public function saved(Master $master): void
    {
        $fullName = implode(' ', array_filter([$master->last_name, $master->first_name]));

        if ($master->user && $master->user->name !== $fullName) {
            $master->user->updateQuietly(['name' => $fullName]);
        }
    }
}
