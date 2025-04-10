<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function phones()
    {
        return $this->hasMany(Phone::class);
    }

    public function master()
    {
        return $this->hasOne(Master::class);
    }

    public function getFullNameAttribute(): string
    {
        return implode(' ', [$this->last_name, $this->first_name, $this->patronymic]);
    }
}
