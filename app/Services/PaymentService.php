<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentRequirement;
use App\Models\StorageBooking;
use App\Models\User;
use App\Models\UserTransaction;
use Carbon\Carbon;

final class PaymentService
{

    public function payForStorageBooking(StorageBooking $storageBooking, float $amount, $useBalance = false): void
    {
        $user = $storageBooking->user;

        if (!$useBalance) {
            $user->deposit($amount, 'StorageCell Number: ' . $storageBooking->cell->number . ' <<< ADD CASH');
        }
        $user->withdraw($amount, 'StorageCell Number: ' . $storageBooking->cell->number . ' <<< STORAGE CELL RENT');

    }

    public function rollbackTransaction(UserTransaction $transaction)
    {

    }

    public function getUserBalance(User $user): float
    {
        return UserTransaction::where('user_id', $user->id)->sum('amount');
    }

    public function createPaymentRequirement($model, float $amountDue, int $expirationDays = null, Carbon $dateTime = null): PaymentRequirement
    {
        if (!method_exists($model, 'paymentRequirements')) {
            throw new \Exception("Модель не поддерживает создание требований на оплату.");
        }

        $dateTime = $dateTime ?? now();

        return PaymentRequirement::create([
            'user_id' => $model->user_id,
            'payable_type' => $model::class,
            'payable_id' => $model->id,
            'amount_due' => $amountDue,
            'due_date' => $expirationDays ? $dateTime->clone()->addDays($expirationDays) : null ,
            'status' => 'pending',
            'created_at' => $dateTime,
            'updated_at' => $dateTime,
        ]);
    }

    public function createPayment($model, $paymentAmount, $paymentMethod, Carbon $dateTime = null)
    {
        if (!method_exists($model, 'payments')) {
            throw new \Exception("Модель не поддерживает создание оплат.");
        }

        $dateTime = $dateTime ?? now();

        return Payment::create([
            'user_id' => $model->user_id,
            'payable_type' => $model::class,
            'payable_id' => $model->id,
            'amount' => $paymentAmount,
            'payment_method' => $paymentMethod,
            'status' => 'pending',
            'created_at' => $dateTime,
            'updated_at' => $dateTime,
        ]);
    }

    public function changePaymentStatus(Payment $payment, $newPaymentStatus): bool
    {
        $payment->fillable(['status']);

        $paymentMethod = $payment->payment_method;
        $currentPaymentStatus = $payment->status;
        $user = User::find($payment->user_id);

        // PAYMENT METHOD CACHE
        if ($currentPaymentStatus != $newPaymentStatus) {
            $result = $payment->update([
                'status' => $newPaymentStatus
            ]);
        }

        // PAYMENT METHOD BALANCE
//        if ($paymentMethod == Payment::METHOD_BALANCE) {
//
//            // TO COMPLETE
//            if ($currentPaymentStatus == Payment::STATUS_PENDING && $newPaymentStatus == Payment::STATUS_COMPLETED) {
//                $this->changeUserBalance($user, $payment->amount);
//            }
//
//            // TO BACK
//            if ($currentPaymentStatus == Payment::STATUS_COMPLETED && $newPaymentStatus != $currentPaymentStatus) {
//                $this->changeUserBalance($user, (-1) * $payment->amount);
//            }
//            $result = $payment->update([
//                'status' => $newPaymentStatus
//            ]);
//        }

        $payment->mergeGuarded(['status']);

        return $result ?? false;
    }

    /**
     * @throws \Exception
     */
    public function changeUserBalance(User $user, float $amount)
    {
        if ($user->real_balance + $user->bonus_balance  < $amount) {
            throw new \Exception("Недостаточно средств на балансе");
        }

        if ($user->real_balance >= $amount) {
            $user->real_balance -= $amount;
        } else {
            $user->bonus_balance -= ($amount - $user->real_balance);
            $user->real_balance = 0;
        }

        return $user->save();
    }
}
