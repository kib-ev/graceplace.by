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

            <div class="mb-3">
                <span class="badge bg-secondary">Всего импортировано строк: {{ $totalPayments }}</span>
                @if($latestPaidAt)
                    <span class="badge bg-light text-dark border">Последняя оплата: {{ \Carbon\Carbon::parse($latestPaidAt)->format('d.m.Y H:i') }}</span>
                @endif
            </div>

            <h5>История импортов</h5>
            <table class="table table-bordered table-sm">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Дата загрузки</th>
                    <th>Файл</th>
                    <th>Месяц отчета</th>
                    <th>Всего строк</th>
                    <th>Добавлено</th>
                    <th>Дубли</th>
                    <th>Кто загрузил</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @forelse($imports as $import)
                    <tr>
                        <td>{{ $import->id }}</td>
                        <td>{{ $import->created_at?->format('d.m.Y H:i') }}</td>
                        <td>{{ $import->original_filename }}</td>
                        <td>{{ $import->report_month?->format('m.Y') ?? '—' }}</td>
                        <td>{{ $import->rows_total }}</td>
                        <td>{{ $import->rows_inserted }}</td>
                        <td>{{ $import->rows_skipped }}</td>
                        <td>{{ $import->importedBy->name ?? '—' }}</td>
                        <td><a href="{{ route('admin.erip-imports.show', $import) }}">Открыть</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">Импортов пока нет</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
