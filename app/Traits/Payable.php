<?php

namespace App\Traits;

use App\Models\Payment;
use App\Models\PaymentRequirement;

trait Payable
{
    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function paymentRequirements()
    {
        return $this->morphMany(PaymentRequirement::class, 'payable');
    }

    public function isPaid()
    {
        $existsPaymentRequirements = count($this->paymentRequirements) >= 1;
        return $existsPaymentRequirements
            && $this->paymentRequirements()->sum('amount_due') == $this->payments()->where('status', Payment::STATUS_COMPLETED)->sum('amount');
    }

    public function leftToPay()
    {
        $diff = $this->paymentRequirements()->where('status', 'pending')->sum('amount_due') - $this->payments()->where('status', 'completed')->sum('amount');
        return max(0, $diff);
    }
}
