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

    public function import()
    {
        return $this->belongsTo(EripPaymentImport::class, 'erip_payment_import_id');
    }
}
