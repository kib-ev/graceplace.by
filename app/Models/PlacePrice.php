<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlacePrice extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'effective_from' => 'date',
    ];

    public function place()
    {
        return $this->belongsTo(Place::class);
    }
}
