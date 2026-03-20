@extends('admin.layouts.app')

@section('style')
    <style>
        .badge-cash { background: lightgreen; padding: 2px 5px; border-radius: 5px; }
        .badge-service { background: lightblue; padding: 2px 5px; border-radius: 5px; }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <h1>Ячейка {{ $storageCell->number }}</h1>
            <p>
                <a href="{{ route('admin.storage-cells.index') }}">← Ячейки для хранения</a>
                | <a href="{{ route('admin.storage-cells.edit', $storageCell) }}">Редактировать ячейку</a>
            </p>
            <hr>

            <div class="card mb-4">
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Номер</strong></td>
                            <td>{{ $storageCell->number }}</td>
                        </tr>
                        <tr>
                            <td><strong>Цена за 30 дней</strong></td>
                            <td>{{ number_format($storageCell->cost_per_month ?? 0, 2) }} BYN</td>
                        </tr>
                        <tr>
                            <td><strong>Код</strong></td>
                            <td>{{ $storageCell->secret ?? '—' }}</td>
                        </tr>
                        @if($storageCell->description)
                        <tr>
                            <td><strong>Описание</strong></td>
                            <td>{{ $storageCell->description }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><strong>Добавить запись</strong></div>
                <div class="card-body">
                    <form action="{{ route('admin.storage-bookings.store') }}" method="post" autocomplete="off">
                        @csrf
                        <input type="hidden" name="model_class" value="{{ \App\Models\StorageCell::class }}">
                        <input type="hidden" name="model_id" value="{{ $storageCell->id }}">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label">Мастер</label>
                                <select name="user_id" class="form-control form-control-sm" required>
                                    <option value=""></option>
                                    @foreach(\App\Models\User::with('master')->role('master')->get()->sortBy(fn($u) => $u->master?->full_name) as $selectUser)
                                        <option value="{{ $selectUser->id }}" @selected($selectUser->id == ($bookings->first()?->user_id))>
                                            {{ $selectUser->master?->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Дата начала</label>
                                <input type="date" name="start_at" class="form-control form-control-sm" required value="{{ $bookings->isNotEmpty() ? $bookings->first()->start_at->copy()->addDays($bookings->first()->duration)->format('Y-m-d') : now()->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Срок (дней)</label>
                                <input type="number" name="duration" class="form-control form-control-sm" value="30" required min="1">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-sm">Добавить</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <h3>Записи (брони)</h3>
            <div class="card">
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Мастер</th>
                            <th>Период</th>
                            <th>Дней</th>
                            <th>Осталось</th>
                            <th>Оплата</th>
                            <th>Статус</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($bookings as $booking)
                            <tr>
                                <td>{{ $booking->id }}</td>
                                <td>
                                    @if($booking->user?->master)
                                        <a href="{{ route('admin.masters.show', $booking->user->master) }}">{{ $booking->user->master->full_name }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    {{ $booking->start_at->format('d.m.Y') }}
                                    —
                                    {{ $booking->start_at->copy()->addDays($booking->duration)->subDay()->format('d.m.Y') }}
                                </td>
                                <td>{{ $booking->duration }}</td>
                                <td>
                                    @if(is_null($booking->finished_at))
                                        {{ $booking->daysLeft() }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td style="white-space: nowrap; text-align: right;">
                                    @if($booking->paymentRequirements->isEmpty())
                                        <span style="color: #c1bebe;">{{ number_format($booking->getExpectedAmount(), 2, '.') }} BYN</span>
                                    @elseif($booking->isPaid())
                                        <b style="color: #000;">{{ number_format($booking->paymentRequirements->first()->getPaidAmount(), 2, '.') }} BYN</b>
                                        @if($booking->payments->where('status', 'completed')->where('amount', '>', 0)->isNotEmpty())
                                            <br>
                                            @foreach($booking->payments->where('status', 'completed') as $payment)
                                                <span class="badge-{{ $payment->payment_method }}" style="font-size: 0.85em; font-weight: bold;">{{ \App\Models\Payment::getPaymentMethods()[$payment->payment_method] ?? ucfirst($payment->payment_method) }}</span>
                                            @endforeach
                                        @endif
                                    @else
                                        <span style="font-weight: 300;">{{ number_format($booking->paymentRequirements->first()->expected_amount ?? 0, 2, '.') }} BYN</span>
                                    @endif
                                </td>
                                <td>
                                    @if(is_null($booking->finished_at))
                                        <span class="badge bg-success">Активна</span>
                                    @else
                                        <span class="badge bg-secondary">Завершена</span>
                                    @endif
                                </td>
                                <td>
                                    <a class="btn btn-sm btn-primary" href="{{ route('admin.storage-bookings.edit', $booking) }}">Редактировать</a>
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.payments.manage', ['payable_type' => \App\Models\StorageBooking::class, 'payable_id' => $booking->id]) }}">Платежи</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-muted">Нет записей</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
