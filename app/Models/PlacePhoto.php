<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlacePhoto extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Получить рабочее место, к которому относится фото.
     */
    public function place()
    {
        return $this->belongsTo(Place::class);
    }
}
