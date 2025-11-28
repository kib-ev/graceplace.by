<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\User\Balance;
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

    public function person()
    {
        return $this->hasOneThrough(Master::class, Phone::class, 'number', 'person_id', 'phone', 'id');
    }

    public function master()
    {
        return $this->hasOne(Master::class);
    }

    public function info()
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

    public function transactions()
    {
        return $this->hasMany(UserTransaction::class);
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
        $fullName = [$this->master->person->last_name, $this->master->person->first_name];
        if($addPatronymic && isset($this->master->person->patronymic)) {
            $fullName[] = $this->master->person->patronymic;
        }
        return implode(' ', $fullName);
    }

    // Метод для пополнения баланса
    public function deposit($amount, $description = null, $createdAt = null)
    {
        $this->real_balance += $amount;
        $this->save();

        // Записываем транзакцию
        $this->transactions()->create([
            'amount' => $amount,
            'type' => 'deposit',
            'description' => $description,
            'balance_after' => $this->real_balance + $this->bonus_balance,
            'created_at' => $createdAt ?? now(),
        ]);
    }

    // Метод для пополнения реальными деньгами и начисления бонусов
    public function depositWithBonus($amount, $description = null, $createdAt = null)
    {
        // Пополнение реальными деньгами
        $this->real_balance += $amount;
        $this->save();

        // Записываем транзакцию реального пополнения
        $this->transactions()->create([
            'amount' => $amount,
            'type' => 'deposit',
            'transaction_type' => 'real',
            'description' => $description,
            'balance_after' => $this->real_balance + $this->bonus_balance,
            'created_at' => $createdAt ?? now(),
        ]);

        // Начисляем бонус, если сумма пополнения соответствует условиям
        $bonusAmount = $this->calculateBonus($amount);

        if ($bonusAmount > 0) {
            $this->bonus_balance += $bonusAmount;
            $this->save();

            // Записываем транзакцию бонусного начисления
            $this->transactions()->create([
                'amount' => $bonusAmount,
                'type' => 'deposit',
                'transaction_type' => 'bonus',
                'description' => 'Бонусное начисление',
                'balance_after' => $this->real_balance + $this->bonus_balance,
                'created_at' => $createdAt ?? now(),
            ]);
        }
    }

    // Метод для расчета бонусов
    protected function calculateBonus($amount)
    {
        $bonus = 0;

        if ($amount >= 100) {
            $bonus = $amount * 0.1; // 10% бонус
        }
        if ($amount >= 200) {
            $bonus = $amount * 0.2; // 20% бонус
        }
        if ($amount >= 300) {
            $bonus = $amount * 0.3; // 30% бонус
        }

        return $bonus; // Бонуса нет для меньших сумм
    }

    // Метод для списания баланса
    public function withdraw($amount, $description = null, $createdAt = null)
    {
        if ($this->real_balance + $this->bonus_balance  < $amount) {
            throw new \Exception("Недостаточно средств на балансе");
        }

        if ($this->real_balance >= $amount) {
            $this->real_balance -= $amount;
        } else {
            $this->bonus_balance -= ($amount - $this->real_balance);
            $this->real_balance = 0;
        }

        $this->save();

        // Записываем транзакцию
        $this->transactions()->create([
            'amount' => -$amount,
            'type' => 'withdrawal',
            'description' => $description,
            'balance_after' => $this->real_balance + $this->bonus_balance,
            'created_at' => $createdAt ?? now(),
        ]);
    }

    public function balances()
    {
        return $this->hasMany(Balance::class);
    }

    public function getBalance(string $type = null): Balance|float // todo REMOVE FLOAT
    {
        if(is_null($type)) { // TODO REMOVE
            return $this->real_balance + $this->bonus_balance;
        }

        // Проверяем, есть ли баланс в загруженных отношениях (чтобы избежать запроса в БД)
        $balance = $this->balances->where('balance_type', $type)->first();

        if (!$balance) {
            // Если баланса нет — создаем его
            $balance = $this->balances()->create([
                'balance_type' => $type,
                'amount' => 0,
                'currency' => 'BYN',
                'status' => 'active',
            ]);

            // Добавляем в коллекцию загруженных балансов (чтобы избежать повторного запроса)
            $this->setRelation('balances', $this->balances->push($balance));
        }

        return $balance;
    }

    public function getDebtAmount()
    {
        return $this->appointments
            ->where('start_at', '<=', now()->startOfDay())
            ->whereNull('canceled_at')
            ->filter(function (Appointment $appointment) {
                return !$appointment->isPaid();
            })
            ->sum(function (Appointment $appointment) {
                return $appointment->leftToPay();
            });
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
