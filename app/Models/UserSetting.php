<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


//    // Константы для методов оплаты
//    const PAYMENT_METHOD_CASH = 'cash';
//    const PAYMENT_METHOD_CARD = 'card';
//    const PAYMENT_METHOD_OTHER = 'other';
//
//    // Метод для получения списка возможных способов оплаты
//    public static function getPaymentMethods(): array
//    {
//        return [
//            self::PAYMENT_METHOD_CASH => 'Наличные',
//            self::PAYMENT_METHOD_CARD => 'Карта',
//            self::PAYMENT_METHOD_OTHER => 'Другое',
//        ];
//    }
}
