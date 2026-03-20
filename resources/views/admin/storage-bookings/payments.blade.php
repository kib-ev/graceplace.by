@extends('admin.layouts.app')

@php
    /** @var \App\Models\StorageBooking $storageBooking */
@endphp

@section('content')
    <div class="container">
        <h1>Платежи — Локер #{{ $storageBooking->id }}</h1>
        <p>
            <a href="{{ route('admin.storage-cells.index') }}">← Ячейки для хранения</a>
            |
            <a href="{{ route('admin.storage-bookings.edit', $storageBooking) }}">Редактировать бронь</a>
        </p>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">
                    @if($storageBooking->user->master ?? null)
                        {{ $storageBooking->user->master->full_name }}
                    @else
                        Пользователь #{{ $storageBooking->user_id }}
                    @endif
                </h5>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Период:</strong></td>
                        <td>{{ $storageBooking->start_at->format('d.m.Y') }} — {{ $storageBooking->start_at->copy()->addDays($storageBooking->duration)->format('d.m.Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Ожидаемая сумма:</strong></td>
                        <td>{{ number_format($storageBooking->getExpectedTotal(), 2, '.') }} BYN</td>
                    </tr>
                    <tr>
                        <td><strong>Остаток к оплате:</strong></td>
                        <td>{{ number_format($storageBooking->leftToPay(), 2, '.') }} BYN</td>
                    </tr>
                    <tr>
                        <td><strong>Оплачено:</strong></td>
                        <td>
                            @if($storageBooking->isPaid())
                                <span class="text-success"><i class="fa fa-check"></i> Да</span>
                            @else
                                <span class="text-muted"><i class="fa fa-minus"></i> Нет</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $e) {{ $e }}<br> @endforeach
            </div>
        @endif

        {{-- Создать требование на оплату --}}
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Создать требование на оплату</h5></div>
            <div class="card-body">
                <form action="{{ route('admin.storage-bookings.payment-requirements.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="storage_booking_id" value="{{ $storageBooking->id }}">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">Сумма</label>
                            <input type="number" name="amount" class="form-control" step="0.01" required
                                   value="{{ $storageBooking->cell->cost_per_month ?? 0 }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Срок оплаты</label>
                            <select name="expiration_days" class="form-control">
                                <option value="30">30 дней</option>
                                <option value="14">14 дней</option>
                                <option value="7">7 дней</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Дата создания</label>
                            <input type="datetime-local" name="created_at" class="form-control"
                                   value="{{ now()->format('Y-m-d\TH:i') }}">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Создать требование</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Требования на оплату --}}
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Требования на оплату</h5></div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Создано</th>
                        <th>Ожидаемая сумма</th>
                        <th>Остаток</th>
                        <th>Срок</th>
                        <th>Статус</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($storageBooking->paymentRequirements as $req)
                        <tr>
                            <td>{{ $req->id }}</td>
                            <td>{{ $req->created_at->format('d.m.Y H:i') }}</td>
                            <td>{{ number_format($req->expected_amount ?? 0, 2) }} BYN</td>
                            <td>{{ number_format($req->remaining_amount ?? 0, 2) }} BYN</td>
                            <td>{{ $req->due_date?->format('d.m.Y') ?? '—' }}</td>
                            <td>{{ $req->status }}</td>
                            <td>
                                <a href="{{ route('admin.payment-requirements.destroy', $req->id) }}"
                                   onclick="return confirm('Удалить требование?')">удалить</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-muted">Нет требований</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Платежи --}}
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Платежи</h5></div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Дата</th>
                        <th>Сумма</th>
                        <th>Метод</th>
                        <th>Статус</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($storageBooking->payments as $payment)
                        <tr>
                            <td>{{ $payment->id }}</td>
                            <td>{{ $payment->created_at->format('d.m.Y H:i') }}</td>
                            <td>{{ number_format($payment->amount, 2) }} BYN</td>
                            <td>{{ $payment->payment_method }}</td>
                            <td>
                                <form action="{{ route('admin.payments.update-status', $payment) }}" method="post" class="d-inline">
                                    @csrf @method('patch')
                                    <select name="status" class="form-select form-select-sm d-inline-block" style="width:auto" onchange="this.form.submit()">
                                        @foreach(\App\Models\Payment::getPaymentStatuses() as $v => $n)
                                            <option value="{{ $v }}" @selected($payment->status == $v)>{{ $n }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td>
                                @if($payment->status === \App\Models\Payment::STATUS_CANCELLED)
                                    <a href="{{ route('admin.payments.destroy', $payment) }}">удалить</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Новый платеж --}}
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Новый платеж</h5></div>
            <div class="card-body">
                <form action="{{ route('admin.storage-bookings.payments.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="storage_booking_id" value="{{ $storageBooking->id }}">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">Дата и время</label>
                            <input type="datetime-local" name="created_at" class="form-control" required
                                   value="{{ now()->format('Y-m-d\TH:i') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Сумма</label>
                            <input type="number" name="amount" class="form-control" step="0.01" required
                                   value="{{ $storageBooking->leftToPay() }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Метод</label>
                            <select name="payment_method" class="form-control" required>
                                <option value="cash">Наличные</option>
                                <option value="service">ЕРИП</option>
                                <option value="card">Карта</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Примечание</label>
                            <input type="text" name="note" class="form-control" placeholder="Необязательно">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">Провести оплату</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
