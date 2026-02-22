<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasSettings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens,
        HasFactory,
        Notifiable,
        HasRoles;
    use HasSettings;

    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'password' => 'hashed',
    ];


    public function master()
    {
        return $this->hasOne(Master::class);
    }


    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function storageBookings()
    {
        return $this->hasMany(StorageBooking::class);
    }


    public function setPhoneAttribute($value): void
    {
        $this->attributes['phone'] = $value;
        $this->attributes['email'] = user_email_from_phone_number($value);
    }

    public function schedule()
    {
        return $this->hasOne(UserSchedule::class);
    }

    public function getFullName($addPatronymic = false): string
    {
        if ($addPatronymic) {
            return implode(' ', array_filter([
                $this->master?->last_name,
                $this->master?->first_name,
                $this->master?->patronymic,
            ]));
        }
        return implode(' ', array_filter([
            $this->master?->last_name,
            $this->master?->first_name,
        ]));
    }

    public function getLateCancellationCount()
    {
        return Appointment::where('user_id', $this->id)
            ->whereNotNull('canceled_at')
            ->where(function ($query) {
                $query->whereRaw('TIMESTAMPDIFF(HOUR, canceled_at, start_at) < 24')
                    ->orWhereColumn('canceled_at', '>=', 'start_at');
            })->count();
    }

    public function mandatoryNotices()
    {
        return $this->belongsToMany(\App\Models\MandatoryNotice::class, 'mandatory_notice_user', 'user_id', 'mandatory_notice_id')
            ->withPivot(['confirmed_at'])
            ->withTimestamps();
    }
}
