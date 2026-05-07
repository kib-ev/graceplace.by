<?php

namespace App\Models;

use App\Traits\HasComments;
use App\Traits\Payable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StorageBooking extends Model
{
    public const ADMIN_CELL_MARKER_ENDING_SOON_DAYS = 3;

    use HasComments;
    use HasFactory;
    use Payable;
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'start_at' => 'datetime',
        'finished_at' => 'datetime',
        'duration' => 'integer',
        'auto_renewal' => 'boolean',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function cell()
    {
        return $this->hasOne(StorageCell::class, 'id', 'model_id');
    }

    public function scopeWithUnpaidLockerRequirement(Builder $builder): Builder
    {
        return $builder
            ->where('start_at', '<=', now())
            ->whereNull('finished_at')
            ->whereHas('paymentRequirements', fn ($q) => $q
                ->where('status', 'pending')
                ->where('remaining_amount', '>', 0)
            );
    }

    public function daysLeft(): int
    {
        $endDate = Carbon::parse($this->start_at)->addDays($this->duration);

        return now()->diffInDays($endDate, false);
    }

    public function lockerPaymentOverdueCalendarDays(): int
    {
        if (! $this->start_at) {
            return 0;
        }

        $start = $this->start_at->copy()->startOfDay();
        $today = now()->copy()->startOfDay();

        return $today->lessThan($start)
            ? 0
            : (int) $start->diffInDays($today);
    }

    public function adminCellListMarkerHexColor(int $endingSoonWithinDays = self::ADMIN_CELL_MARKER_ENDING_SOON_DAYS): string
    {
        if ($this->leftToPay() > 0) {
            return '#dc3545';
        }

        if ($this->daysLeft() <= $endingSoonWithinDays) {
            return '#fd7e14';
        }

        return '#198754';
    }

    public function extend($daysCount = 30): bool
    {
        return $this->update([
            'duration' => $this->duration + $daysCount,
        ]);
    }

    public function getExpectedAmount(): float
    {
        $this->loadMissing('cell');

        return (float) (($this->cell->cost_per_month ?? 0) * ($this->duration / 30));
    }

    public function getPaymentContextLabel(): string
    {
        $cell = $this->cell ?? null;
        $master = $this->user?->master ?? null;

        return implode(', ', [
            'Ячейка',
            $cell?->number ?? '—',
            $master?->full_name ?? '—',
            $this->duration.' '.'дней',
            $this->start_at->format('d.m.Y').'-'.$this->start_at->addDays($this->duration)->format('d.m.Y'),

        ]);
    }
}
