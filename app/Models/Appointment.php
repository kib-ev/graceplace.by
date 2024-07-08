<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'datetime'
    ];

//    protected static function boot()
//    {
//        parent::boot();
//
//        static::addGlobalScope('canceled', function (\Illuminate\Database\Eloquent\Builder $builder) {
//            $builder->where('canceled_at', null);
//        });
//    }
//
//    public function scopeExceptCanceled(\Illuminate\Database\Eloquent\Builder $builder)
//    {
//        return $builder->whereNotNull('canceled_at');
//    }

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
