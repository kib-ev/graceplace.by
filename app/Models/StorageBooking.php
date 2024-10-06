<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StorageBooking extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'start_at' => 'datetime',
        'duration' => 'integer'
    ];

    public function master()
    {
        return $this->hasOne(Master::class, 'id', 'master_id');
    }

    public function cell()
    {
        return $this->hasOne(StorageCell::class, 'id', 'model_id');
    }

    public function daysLeft(): int
    {
        $endDate = Carbon::parse($this->start_at)->addDays($this->duration);
        return now()->diffInDays($endDate,false);
    }
}
