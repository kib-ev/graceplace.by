<?php

const USER_COMMENT_TYPE = 'user';
const ADMIN_COMMENT_TYPE = 'admin';

function is_admin(): bool
{
    $user = auth()->user();
    return $user ? $user->hasRole(['admin']) : false;
}

function is_master($masterId): bool
{
    $userId = auth()->id();
    $master = \App\Services\AppointmentService::getMasterByUserId($userId);

    return $master?->id == $masterId;
}

function short_day_name(\Carbon\Carbon $carbon, bool $uppercase = false): string
{
    $shortDayName = substr(\Illuminate\Support\Carbon::parse($carbon)->locale('ru')->shortDayName, 0, 4);
    return $uppercase ? mb_strtoupper($shortDayName) : $shortDayName;
}
