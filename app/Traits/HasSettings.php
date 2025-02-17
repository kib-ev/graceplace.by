<?php

namespace App\Traits;

use App\Models\UserSetting;

trait HasSettings
{
    // Получение значения настройки по ключу
    public function getSetting($key, $default = null)
    {
        $setting = $this->settings()->where('key', $key)->first();
        return $setting ? json_decode($setting->value, true) : $default;
    }

    // Установка значения настройки
    public function setSetting($key, $value)
    {
        $this->settings()->updateOrCreate(
            ['key' => $key],
            ['value' => json_encode($value)]
        );
    }

    // Удаление настройки
    public function deleteSetting($key)
    {
        $this->settings()->where('key', $key)->delete();
    }

    // Связь с таблицей настроек
    public function settings()
    {
        return $this->hasMany(UserSetting::class);
    }
}
