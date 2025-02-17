<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentRequirement extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'due_date' => 'datetime',
    ];

    /**
     * The possible statuses for a payment requirement.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_OVERDUE = 'overdue';

    /**
     * Get the owning payable model.
     */
    public function payable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user associated with the payment requirement.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the payment requirement is overdue.
     *
     * @return bool
     */
    public function isOverdue()
    {
        return $this->status === self::STATUS_OVERDUE || $this->due_date->isPast();
    }

    /**
     * Mark the payment requirement as paid.
     */
    public function markAsPaid()
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'amount_due' => 0,
        ]);
    }

    // Проверка выполнения требования
    public function isFullyPaid()
    {
        return $this->payments()->sum('amount') >= $this->amount;
    }

    /**
     * Update the amount due based on a payment.
     *
     * @param float $amount
     */
    public function applyPayment($amount)
    {
        $newAmountDue = $this->amount_due - $amount;

        if ($newAmountDue <= 0) {
            $this->markAsPaid();
        } else {
            $this->update(['amount_due' => $newAmountDue]);
        }
    }
}
