@extends('admin.layouts.app')

@section('content')
    <div class="row">
        <div class="col">
            <h1>Импорт ЕРИП #{{ $import->id }}</h1>
            <hr>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <p>
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.erip-imports.index') }}">Назад к импортам</a>
            </p>

            <table class="table table-bordered table-sm mb-4">
                <tr><th style="width: 220px;">Файл</th><td>{{ $import->original_filename }}</td></tr>
                <tr><th>Дата загрузки</th><td>{{ $import->created_at?->format('d.m.Y H:i') }}</td></tr>
                <tr><th>Месяц отчета</th><td>{{ $import->report_month?->format('m.Y') ?? '—' }}</td></tr>
                <tr><th>Всего строк</th><td>{{ $import->rows_total }}</td></tr>
                <tr><th>Добавлено</th><td>{{ $import->rows_inserted }}</td></tr>
                <tr><th>Дубли</th><td>{{ $import->rows_skipped }}</td></tr>
                <tr><th>Кто загрузил</th><td>{{ $import->importedBy->name ?? '—' }}</td></tr>
            </table>

            <h5>Строки отчета</h5>
            <table class="table table-bordered table-striped table-sm">
                <thead>
                <tr>
                    <th>Дата оплаты</th>
                    <th>Сумма</th>
                    <th>Плательщик</th>
                    <th>Телефон</th>
                    <th>№ операции</th>
                    <th>Метод</th>
                    <th>Лицевой счет</th>
                    <th>Статус</th>
                </tr>
                </thead>
                <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->paid_at?->format('d.m.Y H:i') ?? '—' }}</td>
                        <td>{{ number_format((float) $payment->amount, 2) }}</td>
                        <td>{{ $payment->payer_name ?? $payment->payer_raw ?? '—' }}</td>
                        <td>{{ $payment->payer_phone ?? '—' }}</td>
                        <td>{{ $payment->operation_number ?? '—' }}</td>
                        <td>{{ $payment->payment_method ?? '—' }}</td>
                        <td>{{ $payment->account_number ?? '—' }}</td>
                        <td>{{ $payment->status ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">В этом импорте нет добавленных строк</td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $payments->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection
