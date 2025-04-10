<?php

namespace App\Models;

use App\Traits\HasComments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Master extends Model
{
    use HasFactory;
    use HasComments;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->person->first_name . ($this->person->last_name ? ' ' . $this->person->last_name : '');
    }

    public function getPhoneAttribute(): string
    {
        $phone = Cache::remember('master_'.$this->id.'_phone', 60*60, function () {
            return $this->person->phones->first();
        });
        return $phone ? $phone->number : '';
    }

    public function getPhoneNumber(): string
    {
        return $this->getPhoneAttribute();
    }

    public function lastAppointment(): ?Appointment
    {
        return $this->user->appointments->whereNull('canceled_at')->sortByDesc('start_at')->first();
    }
}
