@extends('admin.layouts.app')

@section('content')
    <div class="row">
        <div class="col">
            <h1>Импорт оплат ЕРИП</h1>
            <hr>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Загрузить отчет</h5>
                    <p class="text-muted mb-3">Загружайте ежедневный XLSX-отчет из ЕРИП за текущий месяц. Дубли строк будут пропущены автоматически.</p>
                    <form action="{{ route('admin.erip-imports.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <input type="file" name="report" class="form-control" accept=".xlsx" required>
                        </div>
                        <button class="btn btn-primary" type="submit">Импортировать</button>
                    </form>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.erip-imports.index') }}" class="row g-2 mb-3">
                <div class="col-auto">
                    <label for="erip_date" class="col-form-label">Дата оплат:</label>
                </div>
                <div class="col-auto">
                    <input
                        type="date"
                        id="erip_date"
                        name="erip_date"
                        class="form-control form-control-sm"
                        value="{{ $eripDate }}"
                    >
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary">Показать</button>
                </div>
            </form>

            <h5>Платежи за {{ \Carbon\Carbon::parse($eripDate)->format('d.m.Y') }}</h5>
            <table class="table table-bordered table-sm js-persist-highlight-table" data-highlight-key="erip-imports-row-highlight">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Дата оплаты</th>
                    <th>Номер счета</th>
                    <th>Мастер</th>
                    <th>Сумма</th>
                    <th>№ операции</th>
                    <th>Статус</th>
                </tr>
                </thead>
                <tbody>
                @forelse($payments as $payment)
                    @php
                        $isClosed = ($payment->allocations_count ?? 0) > 0 && (float) $payment->unallocated_amount <= 0;
                        $payerLabel = $payment->payerLabel();
                    @endphp
                    <tr>
                        <td>{{ $payment->id }}</td>
                        <td>{{ $payment->paid_at?->format('d.m.Y H:i') ?? '—' }}</td>
                        <td>{{ $payment->account_number ?? '—' }}</td>
                        <td>
                            @if($payment->matchedMaster)
                                <a href="{{ route('admin.masters.show', $payment->matchedMaster) }}" target="_blank">
                                    {{ $payerLabel !== '' ? $payerLabel : $payment->matchedMaster->full_name }}
                                </a>
                            @elseif($payerLabel !== '')
                                {{ $payerLabel }}
                            @else
                                {{ $payment->payer_phone ?? '—' }}
                            @endif

                            @if(! $payment->matchedMaster)
                                @php
                                    $selectedMasterId = $mastersForSelect->first(function ($master) use ($payment) {
                                        $phone = trim((string) ($master->user?->phone ?? ''));
                                        $paymentPhone = trim((string) ($payment->payer_phone ?? ''));

                                        return $phone !== ''
                                            && $paymentPhone !== ''
                                            && preg_replace('/\D+/', '', $phone) === preg_replace('/\D+/', '', $paymentPhone)
                                            && trim($master->full_name) === trim((string) ($payment->payer_name ?? ''));
                                    })?->id;
                                @endphp
                                <form method="POST" action="{{ route('admin.erip-imports.update-payer', $payment) }}" class="d-flex gap-1 align-items-center mt-1">
                                    @csrf
                                    <input type="hidden" name="erip_date" value="{{ $eripDate }}">
                                    <select name="master_id" class="form-select form-select-sm" required style="min-width: 220px;">
                                        <option value="">Выберите мастера…</option>
                                        @foreach($mastersForSelect as $master)
                                            <option value="{{ $master->id }}" {{ (int) $selectedMasterId === (int) $master->id ? 'selected' : '' }}>
                                                {{ $master->user?->phone }} {{ $master->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">OK</button>
                                </form>
                            @endif
                        </td>
                        <td style="background: {{ $isClosed ? '#9fe3a6' : '#ff9595' }};">{{ number_format((float) $payment->amount, 2) }}</td>
                        <td>{{ $payment->operation_number ?? '—' }}</td>
                        <td>{{ $payment->status ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Платежей за выбранную дату нет</td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            {{ $payments->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
