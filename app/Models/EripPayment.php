<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EripPayment extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'paid_at' => 'datetime',
        'invoice_created_at' => 'datetime',
        'raw_row' => 'array',
    ];

    public function allocations()
    {
        return $this->hasMany(EripPaymentAllocation::class);
    }

    public function getAllocatedAmountAttribute(): float
    {
        if ($this->relationLoaded('allocations')) {
            return (float) $this->allocations->sum('amount');
        }

        return (float) $this->allocations()->sum('amount');
    }

    public function getUnallocatedAmountAttribute(): float
    {
        return max(0, (float) $this->amount - $this->allocated_amount);
    }

    public function getBindingLabel(): string
    {
        return '#'.$this->id
            .' | сч. '.($this->account_number ?? '—')
            .' | '.($this->paid_at?->format('d.m H:i') ?? '—')
            .' | '.($this->payer_phone ?? '—')
            .' | '.number_format((float) $this->unallocated_amount, 2);
    }
}
