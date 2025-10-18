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

    public function createPaymentRequirement($model, float $amountDue, int $expirationDays = null, Carbon $dateTime = null, array $additionalData = []): PaymentRequirement
    {
        if (!method_exists($model, 'paymentRequirements')) {
            throw new \Exception("Model does not support payment requirements.");
        }

        $dateTime = $dateTime ?? now();

        // If amount is 0 (100% discount), mark as paid immediately
        $status = $amountDue == 0 ? 'paid' : ($additionalData['status'] ?? 'pending');
        $remainingAmount = $amountDue == 0 ? 0 : ($additionalData['remaining_amount'] ?? $amountDue);

        $data = [
            'user_id' => $model->user_id,
            'payable_type' => $model::class,
            'payable_id' => $model->id,
            'amount_due' => $amountDue,
            'expected_amount' => $additionalData['expected_amount'] ?? $amountDue,
            'remaining_amount' => $remainingAmount,
            'price_per_hour_snapshot' => $additionalData['price_per_hour_snapshot'] ?? null,
            'due_date' => $expirationDays ? $dateTime->clone()->addDays($expirationDays) : null,
            'status' => $status,
            'created_at' => $dateTime,
            'updated_at' => $dateTime,
        ];

        return PaymentRequirement::create($data);
    }

    public function createPaymentRequirementForAppointment(\App\Models\Appointment $appointment, int $expirationDays = null, Carbon $dateTime = null): PaymentRequirement
    {
        $dateTime = $dateTime ?? $appointment->start_at ?? now();
        $expectedAmount = (new \App\Services\AppointmentService())->calculateAppointmentCost($appointment);
        $pricePerHour = $appointment->place->getPriceForDate($appointment->start_at);

        return $this->createPaymentRequirement(
            $appointment,
            $expectedAmount,
            $expirationDays,
            $dateTime,
            [
                'expected_amount' => $expectedAmount,
                'remaining_amount' => $expectedAmount,
                'price_per_hour_snapshot' => $pricePerHour,
            ]
        );
    }

    public function createPayment($model, $paymentAmount, $paymentMethod, Carbon $dateTime = null, $note = null)
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
            'note' => $note,
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

            if ($newPaymentStatus == Payment::STATUS_COMPLETED && $currentPaymentStatus != Payment::STATUS_COMPLETED) {
                $this->applyPaymentToRequirements($payment);
            }

            if ($currentPaymentStatus == Payment::STATUS_COMPLETED && $newPaymentStatus != Payment::STATUS_COMPLETED) {
                $this->revertPaymentFromRequirements($payment);
            }
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

    protected function applyPaymentToRequirements(Payment $payment)
    {
        $payable = $payment->payable;
        if (!$payable) {
            return;
        }

        $remainingAmount = $payment->amount;

        // Apply payment to pending requirements
        $requirements = $payable->paymentRequirements()
            ->where('status', 'pending')
            ->where('remaining_amount', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($requirements as $requirement) {
            if ($remainingAmount <= 0) {
                break;
            }

            $amountToApply = min($remainingAmount, $requirement->remaining_amount);
            $requirement->applyPayment($amountToApply);
            $remainingAmount -= $amountToApply;
        }
    }

    protected function revertPaymentFromRequirements(Payment $payment)
    {
        $payable = $payment->payable;
        if (!$payable) {
            return;
        }

        // Revert payment by adding amount back to requirements
        $requirements = $payable->paymentRequirements()
            ->orderBy('created_at', 'asc')
            ->get();

        $remainingAmount = $payment->amount;

        foreach ($requirements as $requirement) {
            if ($remainingAmount <= 0) {
                break;
            }

            $requirement->remaining_amount += $remainingAmount;
            $requirement->amount_due += $remainingAmount;
            
            if ($requirement->remaining_amount > 0) {
                $requirement->status = 'pending';
            }
            
            $requirement->save();
            break;
        }
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
