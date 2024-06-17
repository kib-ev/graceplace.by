<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'datetime'
    ];

    public function master()
    {
        return $this->belongsTo(Master::class);
    }

    public function place()
    {
        return $this->belongsTo(Place::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
