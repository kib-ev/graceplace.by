<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $guarded = ['id', 'status'];

    const STATUS_COMPLETED = 'completed';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_PENDING = 'pending';
    const STATUS_CANCELLED = 'cancelled';

    public static function getPaymentStatuses(): array
    {
        // todo translate
        return [
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_REFUNDED => 'Refunded',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    const METHOD_CASH = 'cash';
    const METHOD_CARD = 'card';
    const METHOD_SERVICE = 'service';
    const METHOD_BONUS = 'service';
    const METHOD_OTHER = 'other';

    public function payable()
    {
        return $this->morphTo();
    }

    public function isRefundable()
    {
        return $this->status === self::STATUS_COMPLETED && $this->amount > $this->refunded_amount;
    }

    public function refund($amount)
    {
        if ($this->isRefundable() && $this->amount >= $amount) {
            $this->refunded_amount += $amount;
            $this->status = $this->refunded_amount == $this->amount ? self::STATUS_REFUNDED : $this->status;
            $this->save();
        }
    }

    public function complete()
    {
        $this->status = self::STATUS_COMPLETED;
        $this->save();
    }
}
