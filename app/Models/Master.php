<?php

namespace App\Models;

use App\Traits\HasComments;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Master extends Model
{
    use HasComments;
    use HasFactory;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'user_id', 'user_id');
    }

    public function schedule()
    {
        return $this->hasOne(MasterSchedule::class);
    }

    public function serviceCategories()
    {
        return $this->belongsToMany(ServiceCategory::class, 'master_service_category')
            ->withTimestamps();
    }

    public function getFirstNameAttribute(): string
    {
        return $this->attributes['first_name'] ?? '';
    }

    public function getLastNameAttribute(): string
    {
        return $this->attributes['last_name'] ?? '';
    }

    public function getPatronymicAttribute(): ?string
    {
        return $this->attributes['patronymic'] ?? null;
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
        return $this->attributes['birth_date'] ?? null;
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
            ->whereNull('a2.deleted_at')
            ->where(function (Builder $query) {
                $query->whereNotNull('a2.canceled_at')
                    ->orWhereRaw('TIMESTAMPADD(MINUTE, a2.duration, a2.start_at) <= NOW()');
            })
            ->whereColumn('a2.user_id', 'masters.user_id');
    }

    public function getDebtAmount(): float
    {
        return (float) $this->debtQuery()->sum('pr.remaining_amount');
    }

    public function getFinishedDebtAppointments()
    {
        $ids = $this->debtQuery()->distinct()->pluck('appointments.id');

        return Appointment::query()
            ->with([
                'user.master',
                'place',
                'client',
                'comments.user',
                'paymentRequirements' => fn ($q) => $q->where('status', 'pending'),
            ])
            ->whereIn('id', $ids)
            ->orderBy('start_at')
            ->get();
    }

    public function getDebtAppointments()
    {
        return $this->getFinishedDebtAppointments();
    }

    public function getStorageDebtBookings()
    {
        return StorageBooking::query()
            ->with(['cell', 'paymentRequirements' => fn ($q) => $q->where('status', 'pending')])
            ->where('user_id', $this->user_id)
            ->withUnpaidLockerRequirement()
            ->orderBy('start_at')
            ->get();
    }

    public function getStorageDebtAmount(): float
    {
        return (float) $this->getStorageDebtBookings()->sum(fn (StorageBooking $booking) => $booking->leftToPay());
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
            ->whereNull('appointments.deleted_at')
            ->where(function (Builder $query) {
                $query->whereNotNull('appointments.canceled_at')
                    ->orWhereRaw('TIMESTAMPADD(MINUTE, appointments.duration, appointments.start_at) <= NOW()');
            });
    }
}
