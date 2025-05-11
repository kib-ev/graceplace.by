<?php

namespace App\Models;

use App\Traits\HasComments;
use App\Traits\Payable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StorageBooking extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Payable;
    use HasComments;

    protected $guarded = ['id'];

    protected $casts = [
        'start_at' => 'datetime',
        'finished_at' => 'datetime',
        'duration' => 'integer'
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
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

    public function extend($daysCount = 30): bool
    {
        return $this->update([
            'duration' => $this->duration + $daysCount
        ]);
    }
}
