<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rent extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'start_at' => 'datetime',
        'duration' => 'integer'
    ];

    public function master() {
        return $this->hasOne(Master::class, 'id', 'master_id');
    }
}
