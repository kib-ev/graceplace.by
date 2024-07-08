<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Master extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

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
        $phone = $this->person->phones->first();
        return $phone ? $phone->number : '';
    }

    public function lastAppointment(): ?Appointment
    {
        return \App\Models\Appointment::where('master_id', $this->id)->whereNull('canceled_at')->latest()->first();
    }

}
