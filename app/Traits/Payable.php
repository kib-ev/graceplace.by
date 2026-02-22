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
        if (!$existsPaymentRequirements) {
            return false;
        }

        return $this->paymentRequirements()->sum('remaining_amount') == 0;
    }

    public function leftToPay()
    {
        return max(0, $this->paymentRequirements()->where('status', 'pending')->sum('remaining_amount'));
    }

    public function getExpectedTotal()
    {
        return $this->paymentRequirements()->sum('expected_amount');
    }

    public function createRequirement(float $amount, string $dueDate = null): PaymentRequirement
    {
        return PaymentRequirement::create([
            'user_id'          => $this->user_id,
            'payable_type'     => static::class,
            'payable_id'       => $this->id,
            'amount_due'       => $amount,
            'expected_amount'  => $amount,
            'remaining_amount' => $amount,
            'status'           => PaymentRequirement::STATUS_PENDING,
            'due_date'         => $dueDate,
        ]);
    }

    public function getTotalDiscount()
    {
        $expected = $this->getExpectedTotal();
        $remaining = $this->paymentRequirements()->sum('remaining_amount');
        $paid = $expected - $remaining;
        $actuallyPaid = $this->payments()->where('status', Payment::STATUS_COMPLETED)->sum('amount');

        return max(0, $expected - $actuallyPaid);
    }
}
