@extends('admin.layouts.app')

@php
    /** @var \App\Models\Appointment $appointment */
    /** @var \App\Models\PaymentRequirement $requirement */
@endphp

@section('content')
    <div class="container">
        <h1>Детали записи</h1>

        <div class="card">
            <div class="card-body">

                <h5 class="card-title">Запись №{{ $appointment->id }}</h5>

                <table class="table">
                    <tr>
                        <td><strong>Дата и время:</strong></td>
                        <td>{{ $appointment->start_at->format('d.m.Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Длительность:</strong></td>
                        <td>{{ $appointment->duration }} минут</td>
                    </tr>
                    <tr>
                        <td><strong>Мастер:</strong></td>
                        <td>{{ $appointment->user->name }}</td>
                    </tr>

                    <tr>
                        <td><strong>Баланс пользователя:</strong></td>
                        <td>{{ $appointment->user->real_balance }} BYN</td>
                    </tr>
                    <tr>
                        <td><strong>Бонусы:</strong></td>
                        <td>{{ $appointment->user->bonus_balance }} BYN</td>
                    </tr>

                    <tr>
                        <td><strong>Ожидаемая к оплате сумма:</strong></td>
                        <td>{{ number_format($appointment->getExpectedPrice(), 2, '.') }} BYN</td>
                    </tr>
                    <tr>
                        <td style="width: 250px;"><strong>Оплачено:</strong></td>
                        <td>
                            @if($appointment->isPaid())
                                <span><i style="color: #5fdb64;" class="fa fa-check" aria-hidden="true"></i> Да</span>
                            @else
                                <span><i style="color: #e7e7e7;" class="fa fa-check" aria-hidden="true"></i> Нет</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 250px;"><strong>Всего долг:</strong></td>
                        <td>{{ $appointment->user->appointments->sum(function ($a) { $a->paymentRequirements()->sum('amount_due'); }) }} BYN</td>
                    </tr>
                </table>

            </div>
        </div>

{{--        @dump($appointment->paymentRequirements->sum('amount_due'))--}}

        @if($appointment->paymentRequirements->sum('amount_due') < $appointment->getExpectedPrice())
            <h2 class="mt-4">Создать требование на оплату</h2>
            <form action="{{ route('admin.appointments.payment-requirements.store') }}" method="POST">
                @csrf

                <input type="hidden" name="created_at" value="{{ $appointment->created_at->format('Y-m-d H:i:s') }}">

                <div class="mb-3">
{{--                        <label for="appointment_id" class="form-label">Бронирование (Appointment)</label>--}}
{{--                        <select name="appointment_id" id="appointment_id" class="form-control" required>--}}
{{--                            @foreach(\App\Models\Appointment::all() as $appointment)--}}
{{--                                <option value="{{ $appointment->id }}">{{ $appointment->id }} - {{ $appointment->date }} ({{ $appointment->price }} BYN)</option>--}}
{{--                            @endforeach--}}
{{--                        </select>--}}
                    <input type="hidden" name="appointment_id"  class="form-control" required value="{{ $appointment->id }}">
                </div>

                <div class="mb-3">
                    <label for="amount" class="form-label">Сумма</label>
                    <input type="number" name="amount" id="amount" class="form-control" step="0.01" required value="{{ $appointment->getExpectedPrice() }}">
                </div>

                <div class="mb-3">
                    <label for="expiration_days" class="form-label">Срок оплаты</label>
                    <select class="form-control" name="expiration_days">
                        <option value="30">30 дней</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Создать</button>
            </form>

        @endif

        <h2 class="mt-4">Требования на оплату</h2>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Дата и время создания</th>
                <th>Сумма к оплате</th>
                <th>Срок оплаты</th>
                <th>Статус</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach($appointment->paymentRequirements as $requirement)
                <tr>
                    <td>{{ $requirement->id }}</td>
                    <td>{{ $requirement->created_at->format('d.m.Y H:i') }}</td>
                    <td>{{ number_format($requirement->amount_due, 2) }} BYN</td>
                    <td>{{ $requirement->due_date?->format('d.m.Y') }}</td>
                    <td>{{ $requirement->status }}</td>
                    <td><a href="{{ route('admin.payment-requirements.destroy', $requirement->id) }}">удалить</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <hr>

        <h2 class="mt-4">Платежи</h2>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Дата и время платежа</th>
                <th>Сумма</th>
                <th>Метод оплаты</th>
                <th>Статус оплаты</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach($appointment->payments as $payment)
                <tr>
                    <td>{{ $payment->id }}</td>
                    <td>{{ $payment->created_at->format('d.m.Y H:i') }}</td>
                    <td>{{ number_format($payment->amount, 2) }} BYN</td>
                    <td>{{ $payment->payment_method }}</td>
                    <td>
                        <form action="{{ route('admin.payments.update-status', $payment) }}" method="post">
                            @method('patch')
                            @csrf
                            <input type="hidden" name="payment_id" value="{{ $payment->id }}">
                            <select name="status" id="cash">
                                @foreach(\App\Models\Payment::getPaymentStatuses() as $statusValue => $statusName)
                                    <option value="{{ $statusValue }}" @selected($payment->status == $statusValue)>{{ $statusName }}</option>
                                @endforeach
                            </select>

                            <button type="submit"><span class="fa fa-save"></span></button>
                        </form>
                    </td>
                    <td>
                        @if($payment->status == \App\Models\Payment::STATUS_CANCELLED)
                            <a href="{{ route('admin.payments.destroy', $payment->id) }}">удалить</a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <h2 class="mt-4">Новый платеж</h2>
        <form action="{{ route('admin.appointments.payments.store') }}" method="POST" autocomplete="off">
            @csrf
            <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">

            <div class="mb-3">
                <label for="createdAt" class="form-label">Дата и время</label>
                <input type="datetime-local" name="created_at" id="createdAt" class="form-control" required value="{{ $appointment->end_at->format('Y-m-d H:i') }}">
            </div>

            <div class="mb-3">
                <label for="amount" class="form-label">Сумма</label>
                <input type="number" name="amount" id="amount" class="form-control" step="0.01" required value="{{ $appointment->leftToPay() }}">
            </div>

            <div class="mb-3">
                <label for="payment_method" class="form-label">Метод оплаты</label>
                <select name="payment_method" id="payment_method" class="form-control" required>
                    <option value="cash">Наличные</option>
                    <option value="card">Карта</option>
                    <option value="balance">Баланс</option>
                    <option value="bonus">Бонус</option>
                    <option value="service">Сервис</option>
                    <option value="other">Другое</option>
                </select>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" name="use_balance" id="useBalance" class="" value="1">
                <label for="useBalance" class="form-label">Списать с баланса пользователя ({{ $appointment->user->getBalance() }})</label>
            </div>

            <button type="submit" class="btn btn-primary">Провести оплату</button>
        </form>
    </div>
@endsection
