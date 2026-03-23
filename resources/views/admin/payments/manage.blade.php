@extends('admin.layouts.app')

@section('content')
    <div class="row">
        <div class="col-12">
            <h1>Управление платежами</h1>
            <p>
                @if($payable instanceof \App\Models\Appointment)
                    <a href="{{ route('admin.appointments.edit', $payable) }}">← К записи на рабочее место</a>
                @else
                    <a href="{{ route('admin.storage-bookings.edit', $payable) }}">← К брони ячейки</a>
                @endif
            </p>
            <p class="mb-0"><strong>{{ $payable->getPaymentContextLabel() }}</strong></p>
            <hr>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-warning alert-dismissible fade show">
                    @foreach($errors->all() as $error)
                        <strong>{{ $error }}</strong><br>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($payable->paymentRequirements->count() > 0 && $payable->leftToPay() == 0)
                <div class="alert alert-success mb-3">
                    <i class="fa fa-check-circle"></i> Оплачено полностью
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 col-sm-12">
            <h4>Платежные требования</h4>

            <div class="overflow-scroll">
                @if($payable->paymentRequirements->count() === 0)
                    <form action="{{ route('admin.payables.payment-requirements.store') }}" method="POST" class="mb-3">
                        @csrf
                        <input type="hidden" name="payable_type" value="{{ get_class($payable) }}">
                        <input type="hidden" name="payable_id" value="{{ $payable->id }}">
                        <input type="hidden" name="created_at" value="{{ ($payable->start_at ?? $payable->created_at ?? now())->format('Y-m-d H:i:s') }}">
                        <input type="hidden" name="expiration_days" value="30">

                        <div class="input-group">
                            <input type="number" name="amount" id="amount" class="form-control" step="0.01" required value="{{ number_format($payable->getExpectedAmount(), 2, '.', '') }}" placeholder="Expected: {{ number_format($payable->getExpectedAmount(), 2) }} BYN">
                            <button type="submit" class="btn btn-primary">Создать</button>
                        </div>
                        <small class="text-muted">Ожидаемая расчетная плата: {{ number_format($payable->getExpectedAmount(), 2) }} BYN</small>
                    </form>
                @else
                    <table class="table table-sm table-responsive">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ожидаемая</th>
                            <th>К оплате</th>
                            <th>Остаток</th>
                            <th>Статус</th>
                            <th>Срок</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($payable->paymentRequirements as $requirement)
                            <tr>
                                <td>{{ $requirement->id }}</td>
                                <td>{{ number_format($requirement->expected_amount, 2) }}</td>
                                <td>{{ number_format($requirement->amount_due, 2) }}</td>
                                <td>
                                    <strong>{{ number_format($requirement->remaining_amount, 2) }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $requirement->status === 'paid' ? 'success' : ($requirement->status === 'overdue' ? 'danger' : 'warning') }}">
                                        {{ $requirement->status }}
                                    </span>
                                </td>
                                <td>{{ $requirement->due_date?->format('d.m.Y') }}</td>
                                <td>
                                    @php
                                        $hasCompletedPayments = $payable->payments->where('status', \App\Models\Payment::STATUS_COMPLETED)->count() > 0;
                                    @endphp
                                    @if($hasCompletedPayments)
                                        <span class="text-muted" title="Нельзя удалить: есть завершенные платежи" style="cursor: help;">🔒</span>
                                    @else
                                        <a href="{{ route('admin.payment-requirements.destroy', $requirement->id) }}" class="text-danger" onclick="return confirm('Удалить требование?')">×</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <th colspan="3">Итого к оплате:</th>
                            <th colspan="4"><strong>{{ number_format($payable->leftToPay(), 2) }} BYN</strong></th>
                        </tr>
                        </tfoot>
                    </table>
                @endif
            </div>
        </div>

        <div class="col-lg-6 col-sm-12">
            <h4>Платежи</h4>

            @if($payable->paymentRequirements->count() > 0 && $payable->leftToPay() > 0)
                <form action="{{ route('admin.payables.payments.store') }}" method="POST" autocomplete="off" class="mb-3">
                    @csrf
                    <input type="hidden" name="payable_type" value="{{ get_class($payable) }}">
                    <input type="hidden" name="payable_id" value="{{ $payable->id }}">

                    <div class="input-group mb-2">
                        <input type="datetime-local" name="created_at" class="form-control" required value="{{ old('created_at', now()->format('Y-m-d\TH:i')) }}" title="Дата платежа" style="max-width: 180px;">
                        <input type="number" name="amount" id="payment_amount" class="form-control @error('amount') is-invalid @enderror" step="0.01" required value="{{ number_format($payable->leftToPay(), 2, '.', '') }}" placeholder="Amount">
                        <input type="text" name="note" class="form-control" placeholder="Комментарий">

                        <select name="payment_method" id="payment_method" class="form-select" required style="max-width: 150px;">
                            <option value="service">Сервис ЕРИП</option>
                            <option value="cash">Наличные</option>
                        </select>
                        <button type="submit" class="btn btn-success">Добавить</button>
                    </div>
                    <small class="text-muted">Осталось к оплате: {{ number_format($payable->leftToPay(), 2) }} BYN</small>
                    @error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </form>
            @endif

            @if($payable->payments->count() > 0)
                <table class="table table-sm">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Дата</th>
                        <th>Сумма</th>
                        <th>Метод</th>
                        <th>Комментарий</th>
                        <th>Статус</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($payable->payments as $payment)
                        <tr>
                            <td>{{ $payment->id }}</td>
                            <td>{{ $payment->created_at->format('d.m.Y H:i') }}</td>
                            <td>{{ number_format($payment->amount, 2) }}</td>
                            <td>{{ \App\Models\Payment::getPaymentMethods()[$payment->payment_method] ?? $payment->payment_method }}</td>
                            <td>
                                <small class="text-muted">{{ $payment->note ? Str::limit($payment->note, 30) : '' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'cancelled' ? 'danger' : 'warning') }}">
                                    {{ $payment->status }}
                                </span>
                            </td>
                            <td>
                                @if($payment->status == \App\Models\Payment::STATUS_CANCELLED)
                                    <a href="{{ route('admin.payments.destroy', $payment) }}" class="text-danger" onclick="return confirm('Удалить платеж?')" title="Удалить">×</a>
                                @else
                                    <form action="{{ route('admin.payments.update-status', $payment) }}" method="post" style="display: inline;" onsubmit="return confirm('Отменить платеж?')">
                                        @csrf
                                        @method('patch')
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" class="btn btn-link btn-sm text-warning p-0" title="Отменить">⊘</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr>
                        <th colspan="2">Оплачено:</th>
                        <th colspan="5"><strong>{{ number_format($payable->payments->where('status', 'completed')->sum('amount'), 2) }} BYN</strong></th>
                    </tr>
                    </tfoot>
                </table>
            @else
                @if($payable->paymentRequirements->count() > 0)
                    <p class="text-muted">Нет платежей</p>
                @else
                    <div class="text-muted">
                        Для добавления платежей сначала создайте платежное требование в левой колонке
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
