<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EripPaymentAllocation extends Model
{
    protected $guarded = ['id'];

    public function eripPayment()
    {
        return $this->belongsTo(EripPayment::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
