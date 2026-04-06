@extends('admin.layouts.app')

@section('content')
    <div class="row">
        <div class="col">
            <h1>Платежи E-POS за {{ $date->format('d.m.Y') }}</h1>

            <hr>

            <form method="get" action="{{ route('admin.orders.by-date') }}" class="mb-3">
                <div class="row g-2 align-items-center">
                    <div class="col-auto">
                        <label for="date" class="col-form-label">Дата:</label>
                    </div>
                    <div class="col-auto">
                        <input type="date" name="date" id="date" class="form-control" value="{{ $date->format('Y-m-d') }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">Показать</button>
                    </div>
                </div>
            </form>

            <p>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">← Все счета</a>
            </p>

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if(empty($invoices))
                <div class="alert alert-info">За выбранный день платежей нет</div>
            @else
                <table class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th>№ счёта</th>
                            <th>Плательщик</th>
                            <th>Сумма</th>
                            <th>Дата выставления</th>
                            <th>Дата оплаты</th>
                            <th>Статус счёта</th>
                            <th>Статус оплаты</th>
                            <th>Назначение</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $inv)
                            <tr>
                                <td>{{ $inv['invoiceNumber'] ?? '-' }}</td>
                                <td>{{ Str::limit($inv['payerName'] ?? '-', 40) }}</td>
                                <td>
                                    @if(($inv['totalSum'] ?? 0) > 0)
                                        {{ number_format($inv['totalSum'], 2) }} BYN
                                    @else
                                        <span title="API возвращает 0 для счёта с canEditAmount (сумма указывается при оплате)">—</span>
                                    @endif
                                </td>
                                <td>{{ isset($inv['invoiceDate']) ? \Carbon\Carbon::createFromTimestampMs($inv['invoiceDate'])->format('d.m.Y H:i') : '-' }}</td>
                                <td>{{ isset($inv['paymentDate']) && $inv['paymentDate'] ? \Carbon\Carbon::createFromTimestampMs($inv['paymentDate'])->format('d.m.Y H:i') : '-' }}</td>
                                <td>@php
                                    $invStatuses = [1 => 'Создан', 2 => 'Активен', 3 => 'Оплачен', 4 => 'Закрыт', 5 => 'Частично оплачен'];
                                    echo $invStatuses[$inv['invoiceStatus'] ?? 0] ?? $inv['invoiceStatus'] ?? '-';
                                @endphp</td>
                                <td>@php
                                    $payStatuses = [1 => 'Создан', 10 => 'Проверка', 20 => 'Начало транзакции', 30 => 'Завершено', 200 => 'Ошибка', 210 => 'Ошибка', 220 => 'Ошибка'];
                                    echo $payStatuses[$inv['paymentStatus'] ?? 0] ?? $inv['paymentStatus'] ?? '-';
                                @endphp</td>
                                <td>{{ Str::limit($inv['purposePayment'] ?? '-', 30) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="text-muted small mt-2">
                    Сумма «—»: API WebKassa возвращает totalSum=0 для счетов с изменяемой суммой (аренда). Фактическая сумма оплаты может отображаться в личном кабинете WebKassa.
                </p>
            @endif
        </div>
    </div>
@endsection
