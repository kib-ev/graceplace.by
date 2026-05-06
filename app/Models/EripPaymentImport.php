<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EripPaymentImport extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'report_month' => 'date',
    ];

    public function payments()
    {
        return $this->hasMany(EripPayment::class);
    }

    public function importedBy()
    {
        return $this->belongsTo(User::class, 'imported_by_user_id');
    }
}
