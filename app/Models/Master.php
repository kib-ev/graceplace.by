<?php

namespace App\Models;

use App\Traits\HasComments;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function schedule()
    {
        return $this->hasOne(MasterSchedule::class);
    }

    public function getFirstNameAttribute(): string
    {
        return $this->person?->first_name ?? '';
    }

    public function getLastNameAttribute(): string
    {
        return $this->person?->last_name ?? '';
    }

    public function getPatronymicAttribute(): ?string
    {
        return $this->person?->patronymic;
    }

    public function getFullNameAttribute(): string
    {
        return implode(' ', array_filter([
            $this->last_name,
            $this->first_name,
            $this->patronymic,
        ]));
    }

    public function getBirthDateAttribute(): ?string
    {
        return $this->person?->birth_date;
    }

    public function getPhoneAttribute(): string
    {
        return $this->user?->phone ?? '';
    }

    public function getPhoneNumber(): string
    {
        return $this->phone;
    }

    public function lastAppointment(): ?Appointment
    {
        return $this->user->appointments->whereNull('canceled_at')->sortByDesc('start_at')->first();
    }

    public static function debtAmountSubquery(): Builder
    {
        return PaymentRequirement::query()
            ->selectRaw('COALESCE(SUM(pr2.remaining_amount), 0)')
            ->from('payment_requirements as pr2')
            ->join('appointments as a2', 'a2.id', '=', 'pr2.payable_id')
            ->where('pr2.payable_type', Appointment::class)
            ->where('pr2.status', 'pending')
            ->where('pr2.remaining_amount', '>', 0)
            ->whereNull('a2.canceled_at')
            ->whereNull('a2.deleted_at')
            ->whereRaw('TIMESTAMPADD(MINUTE, a2.duration, a2.start_at) <= NOW()')
            ->whereColumn('a2.user_id', 'masters.user_id');
    }

    public function getDebtAmount(): float
    {
        return (float) $this->debtQuery()->sum('pr.remaining_amount');
    }

    public function getDebtAppointments()
    {
        $ids = $this->debtQuery()->distinct()->pluck('appointments.id');

        return Appointment::query()
            ->with(['place', 'client', 'paymentRequirements' => fn($q) => $q->where('status', 'pending')])
            ->whereIn('id', $ids)
            ->orderBy('start_at')
            ->get();
    }

    private function debtQuery(): Builder
    {
        return Appointment::query()
            ->select('appointments.*')
            ->join('payment_requirements as pr', function ($join) {
                $join->on('pr.payable_id', '=', 'appointments.id')
                     ->where('pr.payable_type', Appointment::class)
                     ->where('pr.status', 'pending')
                     ->where('pr.remaining_amount', '>', 0);
            })
            ->where('appointments.user_id', $this->user_id)
            ->whereNull('appointments.canceled_at')
            ->whereNull('appointments.deleted_at')
            ->whereRaw('TIMESTAMPADD(MINUTE, appointments.duration, appointments.start_at) <= NOW()');
    }
}
